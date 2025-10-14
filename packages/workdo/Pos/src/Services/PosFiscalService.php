<?php

namespace Workdo\Pos\Services;

use Workdo\Pos\Entities\Pos;
use Workdo\Pos\Entities\PosProduct;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\NFeItem;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Jobs\ProcessNFCeJob;

class PosFiscalService
{
    /**
     * Emitir NFC-e automaticamente para uma venda do POS
     *
     * @param Pos $pos
     * @return array
     */
    public static function emitirNFCe(Pos $pos): array
    {
        try {
            // Verificar se o módulo FiscalBR está ativo
            if (!module_is_active('FiscalBR')) {
                return [
                    'success' => false,
                    'message' => 'Módulo Fiscal Brasileiro não está ativo.',
                ];
            }

            // Verificar se deve emitir NFC-e
            if (!$pos->emitir_nfce) {
                return [
                    'success' => false,
                    'message' => 'Emissão de NFC-e desabilitada para esta venda.',
                ];
            }

            // Verificar se já existe NFC-e para esta venda
            if ($pos->nfce_id) {
                return [
                    'success' => false,
                    'message' => 'NFC-e já foi emitida para esta venda.',
                ];
            }

            // Obter configuração fiscal
            $fiscalConfig = FiscalConfig::where('workspace_id', $pos->workspace)->first();
            
            if (!$fiscalConfig) {
                throw new \Exception('Configuração fiscal não encontrada. Configure em /fiscalbr/config');
            }

            // Verificar se tem certificado
            if (!$fiscalConfig->certificado_path) {
                throw new \Exception('Certificado digital não configurado.');
            }

            // Criar NFC-e
            $nfce = self::criarNFCe($pos, $fiscalConfig);

            // Criar itens da NFC-e
            self::criarItensNFCe($nfce, $pos);

            // Atualizar status fiscal do POS
            $pos->update([
                'nfce_id' => $nfce->id,
                'fiscal_status' => 'processando',
            ]);

            // Disparar job para processar NFC-e em background
            ProcessNFCeJob::dispatch($nfce);

            return [
                'success' => true,
                'message' => 'NFC-e em processamento.',
                'nfce_id' => $nfce->id,
            ];

        } catch (\Exception $e) {
            // Atualizar status de erro
            $pos->update([
                'fiscal_status' => 'erro',
                'fiscal_erro_mensagem' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao emitir NFC-e: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Criar registro de NFC-e a partir de uma venda POS
     *
     * @param Pos $pos
     * @param FiscalConfig $fiscalConfig
     * @return NFe
     */
    private static function criarNFCe(Pos $pos, FiscalConfig $fiscalConfig): NFe
    {
        // Obter próximo número de NFC-e
        $nextNumber = $fiscalConfig->getNextNFCeNumber();

        // Obter dados do cliente
        $customer = $pos->customer;
        $customerName = $customer ? $customer->name : 'CONSUMIDOR';
        $customerCpfCnpj = $customer && isset($customer->cpf_cnpj) ? $customer->cpf_cnpj : '';

        // Calcular totais
        $valorProdutos = $pos->getSubTotal();
        $valorDesconto = $pos->getTotalDiscount();
        $valorTotal = $pos->getTotal() + $pos->getTotalTax();

        // Criar NFC-e
        $nfce = NFe::create([
            'workspace_id' => $pos->workspace,
            'tipo' => 'nfce', // Modelo 65
            'numero' => $nextNumber,
            'serie' => $fiscalConfig->nfce_serie ?? '1',
            'data_emissao' => $pos->pos_date ?? now(),
            'natureza_operacao' => 'VENDA',
            'tipo_operacao' => 1, // 1=Saída
            'finalidade' => 1, // 1=Normal
            'consumidor_final' => 1, // 1=Sim
            'presenca' => 1, // 1=Presencial
            'status' => 'rascunho',
            
            // Destinatário
            'destinatario_nome' => $customerName,
            'destinatario_cpf_cnpj' => $customerCpfCnpj,
            'destinatario_tipo' => strlen(preg_replace('/[^0-9]/', '', $customerCpfCnpj)) === 11 ? 'F' : 'J',
            
            // Totais
            'valor_produtos' => $valorProdutos,
            'valor_desconto' => $valorDesconto,
            'valor_total' => $valorTotal,
            'valor_icms' => 0, // Será calculado pelos itens
            'valor_pis' => 0,
            'valor_cofins' => 0,
            
            // Informações adicionais
            'informacoes_complementares' => $pos->fiscal_observacao,
        ]);

        // Atualizar último número
        $fiscalConfig->nfce_ultimo_numero = $nextNumber;
        $fiscalConfig->save();

        return $nfce;
    }

    /**
     * Criar itens da NFC-e a partir dos produtos do POS
     *
     * @param NFe $nfce
     * @param Pos $pos
     * @return void
     */
    private static function criarItensNFCe(NFe $nfce, Pos $pos): void
    {
        $numeroItem = 1;

        foreach ($pos->items as $posProduct) {
            $product = $posProduct->product;

            if (!$product) {
                continue;
            }

            // Obter dados fiscais do produto (se existirem no ProductService)
            $ncm = $posProduct->ncm ?? ($product->ncm ?? '00000000');
            $cfop = $posProduct->cfop ?? '5102'; // CFOP padrão para venda
            $csosn = $posProduct->csosn ?? '102'; // CSOSN padrão para Simples Nacional

            // Calcular valores
            $valorUnitario = $posProduct->price;
            $quantidade = $posProduct->quantity;
            $valorTotal = $valorUnitario * $quantidade;
            $valorDesconto = $posProduct->discount ?? 0;

            // Criar item da NFC-e
            NFeItem::create([
                'nfe_id' => $nfce->id,
                'numero_item' => $numeroItem,
                'codigo_produto' => $product->sku ?? $product->id,
                'descricao' => $product->name,
                'ncm' => $ncm,
                'cest' => $posProduct->cest,
                'cfop' => $cfop,
                'unidade_comercial' => $posProduct->unidade_comercial ?? 'UN',
                'quantidade_comercial' => $quantidade,
                'valor_unitario_comercial' => $valorUnitario,
                'valor_total_bruto' => $valorTotal,
                'codigo_ean' => $posProduct->codigo_ean,
                
                // ICMS
                'icms_origem' => 0, // 0=Nacional
                'icms_csosn' => $csosn,
                'icms_aliquota' => $posProduct->aliquota_icms ?? 0,
                'icms_valor' => $posProduct->valor_icms ?? 0,
                'icms_base_calculo' => $posProduct->base_calculo_icms ?? 0,
                
                // PIS
                'pis_cst' => $posProduct->cst_pis ?? '49',
                'pis_aliquota' => $posProduct->aliquota_pis ?? 0,
                'pis_valor' => $posProduct->valor_pis ?? 0,
                
                // COFINS
                'cofins_cst' => $posProduct->cst_cofins ?? '49',
                'cofins_aliquota' => $posProduct->aliquota_cofins ?? 0,
                'cofins_valor' => $posProduct->valor_cofins ?? 0,
                
                'valor_desconto' => $valorDesconto,
                'valor_total' => $valorTotal - $valorDesconto,
            ]);

            $numeroItem++;
        }
    }

    /**
     * Atualizar status fiscal do POS após processamento da NFC-e
     *
     * @param Pos $pos
     * @param NFe $nfce
     * @return void
     */
    public static function atualizarStatusFiscal(Pos $pos, NFe $nfce): void
    {
        if ($nfce->status === 'autorizada') {
            $pos->update([
                'fiscal_status' => 'emitida',
                'fiscal_emissao_data' => now(),
                'fiscal_numero_nfce' => $nfce->numero,
                'fiscal_chave_acesso' => $nfce->chave_acesso,
                'fiscal_erro_mensagem' => null,
            ]);
        } elseif ($nfce->status === 'erro' || $nfce->status === 'rejeitada') {
            $pos->update([
                'fiscal_status' => 'erro',
                'fiscal_erro_mensagem' => $nfce->sefaz_mensagem ?? 'Erro ao processar NFC-e',
            ]);
        }
    }

    /**
     * Verificar se pode emitir NFC-e
     *
     * @param Pos $pos
     * @return bool
     */
    public static function podeEmitirNFCe(Pos $pos): bool
    {
        // Verificar se módulo está ativo
        if (!module_is_active('FiscalBR')) {
            return false;
        }

        // Verificar se emissão está habilitada
        if (!$pos->emitir_nfce) {
            return false;
        }

        // Verificar se já foi emitida
        if ($pos->nfce_id) {
            return false;
        }

        // Verificar se tem configuração fiscal
        $fiscalConfig = FiscalConfig::where('workspace_id', $pos->workspace)->first();
        
        if (!$fiscalConfig || !$fiscalConfig->certificado_path) {
            return false;
        }

        return true;
    }

    /**
     * Cancelar NFC-e vinculada a uma venda POS
     *
     * @param Pos $pos
     * @param string $justificativa
     * @return array
     */
    public static function cancelarNFCe(Pos $pos, string $justificativa): array
    {
        try {
            if (!$pos->nfce_id) {
                throw new \Exception('Não há NFC-e vinculada a esta venda.');
            }

            $nfce = NFe::find($pos->nfce_id);

            if (!$nfce) {
                throw new \Exception('NFC-e não encontrada.');
            }

            if (!$nfce->canBeCancelled()) {
                throw new \Exception('NFC-e não pode ser cancelada.');
            }

            // Importar serviço de eventos
            $eventService = new \Workdo\FiscalBR\Library\NFeEventService($nfce);
            $result = $eventService->cancelar($justificativa);

            if ($result['success']) {
                $pos->update([
                    'fiscal_status' => 'cancelada',
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao cancelar NFC-e: ' . $e->getMessage(),
            ];
        }
    }
}

