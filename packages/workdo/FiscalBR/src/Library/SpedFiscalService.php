<?php

namespace Workdo\FiscalBR\Library;

use Workdo\FiscalBR\Entities\Sped;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Entities\NFe;
use Carbon\Carbon;

class SpedFiscalService
{
    protected $workspaceId;
    protected $ano;
    protected $mes;
    protected $fiscalConfig;
    protected $linhas = [];

    public function __construct(int $workspaceId, int $ano, int $mes)
    {
        $this->workspaceId = $workspaceId;
        $this->ano = $ano;
        $this->mes = $mes;
        $this->fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->firstOrFail();
    }

    /**
     * Generate SPED Fiscal file
     *
     * @return string
     */
    public function generate(): string
    {
        $this->linhas = [];

        // Bloco 0: Abertura, Identificação e Referências
        $this->generateBloco0();

        // Bloco C: Documentos Fiscais I - Mercadorias (ICMS/IPI)
        $this->generateBlocoC();

        // Bloco E: Apuração do ICMS e do IPI
        $this->generateBlocoE();

        // Bloco H: Inventário Físico
        $this->generateBlocoH();

        // Bloco 9: Controle e Encerramento do Arquivo Digital
        $this->generateBloco9();

        return implode("\r\n", $this->linhas);
    }

    /**
     * Generate Bloco 0 - Abertura, Identificação e Referências
     */
    private function generateBloco0(): void
    {
        // Registro 0000: Abertura do Arquivo Digital e Identificação da Entidade
        $this->addLinha('0000', [
            '014', // Código da versão do leiaute
            '0', // Código da finalidade do arquivo (0=Remessa do arquivo original)
            $this->formatDate(Carbon::create($this->ano, $this->mes, 1)->startOfMonth()), // Data inicial
            $this->formatDate(Carbon::create($this->ano, $this->mes, 1)->endOfMonth()), // Data final
            $this->fiscalConfig->razao_social,
            $this->formatCNPJ($this->fiscalConfig->cnpj),
            '', // CPF (vazio para PJ)
            $this->getUF(),
            $this->fiscalConfig->inscricao_estadual ?? '',
            $this->getCodMunicipio(),
            '', // IM (Inscrição Municipal)
            '', // SUFRAMA
            $this->getPerfilEscrituração(), // Perfil de apresentação
            $this->getAtividadeIndustrial(), // Indicador de atividade
        ]);

        // Registro 0001: Abertura do Bloco 0
        $this->addLinha('0001', ['0']); // 0=Bloco com dados informados

        // Registro 0005: Dados Complementares da Entidade
        $this->addLinha('0005', [
            $this->fiscalConfig->nome_fantasia ?? $this->fiscalConfig->razao_social,
            $this->getCEP(),
            $this->getEndereco(),
            $this->getNumero(),
            $this->getComplemento(),
            $this->getBairro(),
            '', // Fone
            '', // Fax
            '', // Email
        ]);

        // Registro 0015: Dados do Contribuinte Substituto
        // (Opcional - não implementado nesta versão)

        // Registro 0100: Dados do Contabilista
        $this->addLinha('0100', [
            'CONTADOR RESPONSAVEL', // Nome
            '00000000000', // CPF
            '00000-0', // CRC
            '', // CNPJ
            '', // CEP
            '', // Endereço
            '', // Número
            '', // Complemento
            '', // Bairro
            '', // Fone
            '', // Fax
            '', // Email
            $this->getCodMunicipio(),
        ]);

        // Registro 0150: Tabela de Cadastro do Participante
        $this->generateRegistro0150();

        // Registro 0190: Identificação das Unidades de Medida
        $this->addLinha('0190', ['UN', 'UNIDADE']);
        $this->addLinha('0190', ['PC', 'PEÇA']);
        $this->addLinha('0190', ['KG', 'QUILOGRAMA']);
        $this->addLinha('0190', ['MT', 'METRO']);
        $this->addLinha('0190', ['LT', 'LITRO']);

        // Registro 0200: Tabela de Identificação do Item (Mercadoria/Produto e Serviços)
        $this->generateRegistro0200();

        // Registro 0990: Encerramento do Bloco 0
        $this->addLinha('0990', [count(array_filter($this->linhas, fn($l) => str_starts_with($l, '0'))) + 1]);
    }

    /**
     * Generate Registro 0150 - Cadastro de Participantes
     */
    private function generateRegistro0150(): void
    {
        $dataInicial = Carbon::create($this->ano, $this->mes, 1)->startOfMonth();
        $dataFinal = Carbon::create($this->ano, $this->mes, 1)->endOfMonth();

        // Get all unique participants from NFe in the period
        $nfes = NFe::where('workspace_id', $this->workspaceId)
            ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
            ->whereIn('status', ['autorizada', 'cancelada'])
            ->get();

        $participantes = [];

        foreach ($nfes as $nfe) {
            if ($nfe->destinatario_cpf_cnpj && !isset($participantes[$nfe->destinatario_cpf_cnpj])) {
                $cpfCnpj = preg_replace('/[^0-9]/', '', $nfe->destinatario_cpf_cnpj);
                $isCPF = strlen($cpfCnpj) === 11;

                $this->addLinha('0150', [
                    $isCPF ? '1' : '2', // Código do participante (1=CPF, 2=CNPJ)
                    $nfe->destinatario_nome,
                    '000', // Código do país (000=Brasil)
                    $isCPF ? $cpfCnpj : '', // CPF
                    $isCPF ? '' : $cpfCnpj, // CNPJ
                    $nfe->destinatario_uf ?? '',
                    $nfe->destinatario_ie ?? '',
                    $this->getCodMunicipioFromUF($nfe->destinatario_uf ?? ''),
                    '', // SUFRAMA
                    $nfe->destinatario_endereco ?? '',
                    '', // Número
                    '', // Complemento
                    '', // Bairro
                ]);

                $participantes[$nfe->destinatario_cpf_cnpj] = true;
            }
        }
    }

    /**
     * Generate Registro 0200 - Tabela de Itens
     */
    private function generateRegistro0200(): void
    {
        $dataInicial = Carbon::create($this->ano, $this->mes, 1)->startOfMonth();
        $dataFinal = Carbon::create($this->ano, $this->mes, 1)->endOfMonth();

        // Get all unique items from NFe in the period
        $nfes = NFe::with('items')
            ->where('workspace_id', $this->workspaceId)
            ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
            ->whereIn('status', ['autorizada', 'cancelada'])
            ->get();

        $itens = [];

        foreach ($nfes as $nfe) {
            foreach ($nfe->items as $item) {
                if (!isset($itens[$item->codigo_produto])) {
                    $this->addLinha('0200', [
                        $item->codigo_produto,
                        $item->descricao,
                        '', // Código de barra
                        '', // Código da mercadoria ANP
                        '00', // Tipo do item (00=Mercadoria para revenda)
                        $item->unidade,
                        $item->ncm ?? '00000000',
                        '', // EX IPI
                        '', // Código de gênero
                        '', // Código de serviço
                        '', // Alíquota ICMS
                    ]);

                    $itens[$item->codigo_produto] = true;
                }
            }
        }
    }

    /**
     * Generate Bloco C - Documentos Fiscais I
     */
    private function generateBlocoC(): void
    {
        // Registro C001: Abertura do Bloco C
        $this->addLinha('C001', ['0']); // 0=Bloco com dados informados

        $dataInicial = Carbon::create($this->ano, $this->mes, 1)->startOfMonth();
        $dataFinal = Carbon::create($this->ano, $this->mes, 1)->endOfMonth();

        // Get all NFe in the period
        $nfes = NFe::with('items')
            ->where('workspace_id', $this->workspaceId)
            ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
            ->whereIn('status', ['autorizada', 'cancelada'])
            ->orderBy('data_emissao')
            ->orderBy('numero')
            ->get();

        foreach ($nfes as $nfe) {
            if ($nfe->tipo === 'nfe' || $nfe->tipo === 'nfce') {
                $this->generateRegistroC100($nfe);
            }
        }

        // Registro C990: Encerramento do Bloco C
        $this->addLinha('C990', [count(array_filter($this->linhas, fn($l) => str_starts_with($l, 'C'))) + 1]);
    }

    /**
     * Generate Registro C100 - Nota Fiscal (código 01), Nota Fiscal Avulsa (código 1B), Nota Fiscal de Produtor (código 04), NF-e (código 55) e NFC-e (código 65)
     */
    private function generateRegistroC100(NFe $nfe): void
    {
        $this->addLinha('C100', [
            '0', // Indicador do tipo de operação (0=Entrada, 1=Saída)
            '1', // Indicador do emitente (0=Emissão própria, 1=Terceiros)
            $this->formatCPFCNPJ($nfe->destinatario_cpf_cnpj ?? ''),
            $nfe->modelo, // Código do modelo (55=NF-e, 65=NFC-e)
            '1', // Código da situação do documento (00=Regular, 02=Cancelado)
            $nfe->serie,
            $nfe->numero,
            $nfe->chave_acesso ?? '',
            $this->formatDate($nfe->data_emissao),
            $this->formatDate($nfe->data_emissao), // Data de entrada/saída
            $this->formatDecimal($nfe->valor_total),
            '1', // Indicador do tipo de pagamento (0=À vista, 1=A prazo, 9=Sem pagamento)
            $this->formatDecimal($nfe->valor_desconto),
            $this->formatDecimal(0), // Abatimento
            $this->formatDecimal(0), // Outras despesas
            $this->formatDecimal($nfe->valor_icms),
            $this->formatDecimal(0), // Base de cálculo do ICMS ST
            $this->formatDecimal(0), // Valor do ICMS ST
            $this->formatDecimal($nfe->valor_ipi),
            $this->formatDecimal($nfe->valor_pis),
            $this->formatDecimal($nfe->valor_cofins),
        ]);

        // Registro C170: Itens do Documento
        foreach ($nfe->items as $item) {
            $this->generateRegistroC170($item);
        }

        // Registro C190: Registro Analítico do Documento (Código 01, 1B, 04, 55 e 65)
        $this->generateRegistroC190($nfe);
    }

    /**
     * Generate Registro C170 - Itens do Documento
     */
    private function generateRegistroC170($item): void
    {
        $this->addLinha('C170', [
            $item->numero_item,
            $item->codigo_produto,
            $item->descricao,
            $this->formatDecimal($item->quantidade),
            $item->unidade,
            $this->formatDecimal($item->valor_unitario),
            $this->formatDecimal($item->valor_total),
            $this->formatDecimal($item->valor_desconto ?? 0),
            '0', // Indicador de movimentação física (0=Sim, 1=Não)
            $item->icms_cst ?? '000',
            $item->cfop,
            $item->ncm ?? '00000000',
        ]);
    }

    /**
     * Generate Registro C190 - Registro Analítico do Documento
     */
    private function generateRegistroC190(NFe $nfe): void
    {
        // Group items by CST and CFOP
        $grupos = [];

        foreach ($nfe->items as $item) {
            $key = ($item->icms_cst ?? '000') . '_' . $item->cfop;

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'cst' => $item->icms_cst ?? '000',
                    'cfop' => $item->cfop,
                    'valor' => 0,
                    'base_icms' => 0,
                    'valor_icms' => 0,
                ];
            }

            $grupos[$key]['valor'] += $item->valor_total;
            $grupos[$key]['base_icms'] += $item->icms_base_calculo ?? 0;
            $grupos[$key]['valor_icms'] += $item->icms_valor ?? 0;
        }

        foreach ($grupos as $grupo) {
            $this->addLinha('C190', [
                $grupo['cst'],
                $grupo['cfop'],
                '0', // Alíquota do ICMS
                $this->formatDecimal($grupo['valor']),
                $this->formatDecimal($grupo['base_icms']),
                $this->formatDecimal($grupo['valor_icms']),
                $this->formatDecimal(0), // Base de cálculo do ICMS ST
                $this->formatDecimal(0), // Valor do ICMS ST
                $this->formatDecimal(0), // Valor do IPI
                '000', // Código da observação
            ]);
        }
    }

    /**
     * Generate Bloco E - Apuração do ICMS e do IPI
     */
    private function generateBlocoE(): void
    {
        // Registro E001: Abertura do Bloco E
        $this->addLinha('E001', ['0']); // 0=Bloco com dados informados

        // Registro E100: Período da Apuração do ICMS
        $this->addLinha('E100', [
            $this->formatDate(Carbon::create($this->ano, $this->mes, 1)->startOfMonth()),
            $this->formatDate(Carbon::create($this->ano, $this->mes, 1)->endOfMonth()),
        ]);

        // Registro E110: Apuração do ICMS - Operações Próprias
        $this->generateRegistroE110();

        // Registro E990: Encerramento do Bloco E
        $this->addLinha('E990', [count(array_filter($this->linhas, fn($l) => str_starts_with($l, 'E'))) + 1]);
    }

    /**
     * Generate Registro E110 - Apuração do ICMS
     */
    private function generateRegistroE110(): void
    {
        $dataInicial = Carbon::create($this->ano, $this->mes, 1)->startOfMonth();
        $dataFinal = Carbon::create($this->ano, $this->mes, 1)->endOfMonth();

        // Calculate totals from NFe
        $totais = NFe::where('workspace_id', $this->workspaceId)
            ->whereBetween('data_emissao', [$dataInicial, $dataFinal])
            ->where('status', 'autorizada')
            ->selectRaw('
                SUM(valor_icms) as total_icms,
                SUM(CASE WHEN tipo = "nfe" THEN valor_icms ELSE 0 END) as debito_icms,
                SUM(CASE WHEN tipo = "nfce" THEN valor_icms ELSE 0 END) as credito_icms
            ')
            ->first();

        $this->addLinha('E110', [
            $this->formatDecimal($totais->total_icms ?? 0), // Valor total dos débitos
            $this->formatDecimal(0), // Ajustes a débito
            $this->formatDecimal($totais->debito_icms ?? 0), // Valor total dos débitos
            $this->formatDecimal($totais->credito_icms ?? 0), // Valor total dos créditos
            $this->formatDecimal(0), // Ajustes a crédito
            $this->formatDecimal(0), // Outros débitos
            $this->formatDecimal(($totais->debito_icms ?? 0) - ($totais->credito_icms ?? 0)), // Saldo devedor
            $this->formatDecimal(0), // Deduções
            $this->formatDecimal(0), // ICMS a recolher
            $this->formatDecimal(0), // Saldo credor a transportar
        ]);
    }

    /**
     * Generate Bloco H - Inventário Físico
     */
    private function generateBlocoH(): void
    {
        // Registro H001: Abertura do Bloco H
        $this->addLinha('H001', ['1']); // 1=Bloco sem dados informados (inventário não obrigatório nesta versão)

        // Registro H990: Encerramento do Bloco H
        $this->addLinha('H990', [count(array_filter($this->linhas, fn($l) => str_starts_with($l, 'H'))) + 1]);
    }

    /**
     * Generate Bloco 9 - Controle e Encerramento
     */
    private function generateBloco9(): void
    {
        // Registro 9001: Abertura do Bloco 9
        $this->addLinha('9001', ['0']);

        // Registro 9900: Registros do Arquivo
        $registros = [];
        foreach ($this->linhas as $linha) {
            $reg = substr($linha, 1, 4);
            if (!isset($registros[$reg])) {
                $registros[$reg] = 0;
            }
            $registros[$reg]++;
        }

        foreach ($registros as $reg => $qtd) {
            $this->addLinha('9900', [$reg, $qtd]);
        }

        // Add 9900 and 9990 and 9999
        $this->addLinha('9900', ['9900', count($registros) + 2]);
        $this->addLinha('9900', ['9990', 1]);
        $this->addLinha('9900', ['9999', 1]);

        // Registro 9990: Encerramento do Bloco 9
        $this->addLinha('9990', [count(array_filter($this->linhas, fn($l) => str_starts_with($l, '9'))) + 1]);

        // Registro 9999: Encerramento do Arquivo Digital
        $this->addLinha('9999', [count($this->linhas) + 1]);
    }

    /**
     * Add line to SPED file
     */
    private function addLinha(string $registro, array $campos): void
    {
        $linha = '|' . $registro . '|' . implode('|', $campos) . '|';
        $this->linhas[] = $linha;
    }

    /**
     * Helper methods for formatting
     */
    private function formatDate($date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('dmY');
        }
        return Carbon::parse($date)->format('dmY');
    }

    private function formatDecimal($value): string
    {
        return number_format((float)$value, 2, ',', '');
    }

    private function formatCNPJ($cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    private function formatCPFCNPJ($cpfCnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cpfCnpj);
    }

    private function getUF(): string
    {
        // Extract UF from CNPJ or config
        return 'SP'; // TODO: Get from config
    }

    private function getCodMunicipio(): string
    {
        return '3550308'; // TODO: Get from config (São Paulo)
    }

    private function getCodMunicipioFromUF($uf): string
    {
        return '0000000'; // TODO: Implement municipality code lookup
    }

    private function getCEP(): string
    {
        return '00000000'; // TODO: Get from config
    }

    private function getEndereco(): string
    {
        return 'RUA EXEMPLO'; // TODO: Get from config
    }

    private function getNumero(): string
    {
        return '123'; // TODO: Get from config
    }

    private function getComplemento(): string
    {
        return ''; // TODO: Get from config
    }

    private function getBairro(): string
    {
        return 'CENTRO'; // TODO: Get from config
    }

    private function getPerfilEscrituração(): string
    {
        return 'A'; // A=Perfil A (completo)
    }

    private function getAtividadeIndustrial(): string
    {
        return '0'; // 0=Industrial ou equiparado a industrial
    }
}

