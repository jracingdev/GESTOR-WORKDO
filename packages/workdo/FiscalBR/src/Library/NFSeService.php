<?php

namespace Workdo\FiscalBR\Library;

use Workdo\FiscalBR\Entities\NFSe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Carbon\Carbon;

class NFSeService
{
    protected $nfse;
    protected $fiscalConfig;

    public function __construct(NFSe $nfse)
    {
        $this->nfse = $nfse;
        $this->fiscalConfig = FiscalConfig::where('workspace_id', $nfse->workspace_id)->firstOrFail();
    }

    /**
     * Generate RPS (Recibo Provisório de Serviços)
     *
     * @return string XML do RPS
     */
    public function generateRPS(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Rps xmlns="http://www.abrasf.org.br/nfse.xsd">';
        
        // InfDeclaracaoPrestacaoServico
        $xml .= '<InfDeclaracaoPrestacaoServico>';
        
        // Rps
        $xml .= '<Rps>';
        $xml .= '<IdentificacaoRps>';
        $xml .= '<Numero>' . $this->nfse->numero_rps . '</Numero>';
        $xml .= '<Serie>' . $this->nfse->serie_rps . '</Serie>';
        $xml .= '<Tipo>1</Tipo>'; // 1=RPS, 2=Nota Fiscal Conjugada (Mista), 3=Cupom
        $xml .= '</IdentificacaoRps>';
        $xml .= '<DataEmissao>' . $this->nfse->data_emissao->format('Y-m-d') . '</DataEmissao>';
        $xml .= '<Status>1</Status>'; // 1=Normal, 2=Cancelado
        $xml .= '</Rps>';
        
        // Competencia
        $xml .= '<Competencia>' . $this->nfse->data_emissao->format('Y-m-d') . '</Competencia>';
        
        // Servico
        $xml .= '<Servico>';
        $xml .= '<Valores>';
        $xml .= '<ValorServicos>' . number_format($this->nfse->valor_servicos, 2, '.', '') . '</ValorServicos>';
        $xml .= '<ValorDeducoes>' . number_format($this->nfse->valor_deducoes, 2, '.', '') . '</ValorDeducoes>';
        $xml .= '<ValorPis>' . number_format($this->nfse->valor_pis, 2, '.', '') . '</ValorPis>';
        $xml .= '<ValorCofins>' . number_format($this->nfse->valor_cofins, 2, '.', '') . '</ValorCofins>';
        $xml .= '<ValorInss>' . number_format($this->nfse->valor_inss, 2, '.', '') . '</ValorInss>';
        $xml .= '<ValorIr>' . number_format($this->nfse->valor_ir, 2, '.', '') . '</ValorIr>';
        $xml .= '<ValorCsll>' . number_format($this->nfse->valor_csll, 2, '.', '') . '</ValorCsll>';
        $xml .= '<OutrasRetencoes>' . number_format($this->nfse->valor_outras_retencoes, 2, '.', '') . '</OutrasRetencoes>';
        $xml .= '<ValorIss>' . number_format($this->nfse->valor_iss, 2, '.', '') . '</ValorIss>';
        $xml .= '<Aliquota>' . number_format($this->nfse->aliquota_iss, 4, '.', '') . '</Aliquota>';
        $xml .= '<DescontoIncondicionado>' . number_format($this->nfse->desconto_incondicionado, 2, '.', '') . '</DescontoIncondicionado>';
        $xml .= '<DescontoCondicionado>' . number_format($this->nfse->desconto_condicionado, 2, '.', '') . '</DescontoCondicionado>';
        $xml .= '</Valores>';
        
        $xml .= '<IssRetido>' . ($this->nfse->iss_retido === 'sim' ? '1' : '2') . '</IssRetido>';
        $xml .= '<ItemListaServico>' . $this->nfse->item_lista_servico . '</ItemListaServico>';
        
        if ($this->nfse->codigo_cnae) {
            $xml .= '<CodigoCnae>' . $this->nfse->codigo_cnae . '</CodigoCnae>';
        }
        
        if ($this->nfse->codigo_tributacao_municipio) {
            $xml .= '<CodigoTributacaoMunicipio>' . $this->nfse->codigo_tributacao_municipio . '</CodigoTributacaoMunicipio>';
        }
        
        $xml .= '<Discriminacao><![CDATA[' . $this->nfse->descricao_servico . ']]></Discriminacao>';
        $xml .= '<CodigoMunicipio>' . $this->nfse->municipio_prestacao . '</CodigoMunicipio>';
        $xml .= '<ExigibilidadeISS>' . $this->nfse->exigibilidade_iss . '</ExigibilidadeISS>';
        
        if ($this->nfse->municipio_incidencia) {
            $xml .= '<MunicipioIncidencia>' . $this->nfse->municipio_incidencia . '</MunicipioIncidencia>';
        }
        
        $xml .= '</Servico>';
        
        // Prestador
        $xml .= '<Prestador>';
        $xml .= '<CpfCnpj>';
        $cnpj = preg_replace('/[^0-9]/', '', $this->fiscalConfig->cnpj);
        $xml .= '<Cnpj>' . $cnpj . '</Cnpj>';
        $xml .= '</CpfCnpj>';
        
        if ($this->fiscalConfig->inscricao_municipal) {
            $xml .= '<InscricaoMunicipal>' . $this->fiscalConfig->inscricao_municipal . '</InscricaoMunicipal>';
        }
        
        $xml .= '</Prestador>';
        
        // Tomador
        $xml .= '<Tomador>';
        $xml .= '<IdentificacaoTomador>';
        $xml .= '<CpfCnpj>';
        
        $cpfCnpj = preg_replace('/[^0-9]/', '', $this->nfse->tomador_cpf_cnpj);
        if (strlen($cpfCnpj) === 11) {
            $xml .= '<Cpf>' . $cpfCnpj . '</Cpf>';
        } else {
            $xml .= '<Cnpj>' . $cpfCnpj . '</Cnpj>';
        }
        
        $xml .= '</CpfCnpj>';
        
        if ($this->nfse->tomador_inscricao_municipal) {
            $xml .= '<InscricaoMunicipal>' . $this->nfse->tomador_inscricao_municipal . '</InscricaoMunicipal>';
        }
        
        $xml .= '</IdentificacaoTomador>';
        
        $xml .= '<RazaoSocial>' . htmlspecialchars($this->nfse->tomador_nome) . '</RazaoSocial>';
        
        if ($this->nfse->tomador_endereco) {
            $xml .= '<Endereco>';
            $xml .= '<Endereco>' . htmlspecialchars($this->nfse->tomador_endereco) . '</Endereco>';
            
            if ($this->nfse->tomador_numero) {
                $xml .= '<Numero>' . $this->nfse->tomador_numero . '</Numero>';
            }
            
            if ($this->nfse->tomador_complemento) {
                $xml .= '<Complemento>' . htmlspecialchars($this->nfse->tomador_complemento) . '</Complemento>';
            }
            
            if ($this->nfse->tomador_bairro) {
                $xml .= '<Bairro>' . htmlspecialchars($this->nfse->tomador_bairro) . '</Bairro>';
            }
            
            if ($this->nfse->tomador_cidade) {
                $xml .= '<CodigoMunicipio>' . $this->nfse->municipio_prestacao . '</CodigoMunicipio>';
            }
            
            if ($this->nfse->tomador_uf) {
                $xml .= '<Uf>' . $this->nfse->tomador_uf . '</Uf>';
            }
            
            if ($this->nfse->tomador_cep) {
                $xml .= '<Cep>' . preg_replace('/[^0-9]/', '', $this->nfse->tomador_cep) . '</Cep>';
            }
            
            $xml .= '</Endereco>';
        }
        
        if ($this->nfse->tomador_email || $this->nfse->tomador_telefone) {
            $xml .= '<Contato>';
            
            if ($this->nfse->tomador_telefone) {
                $xml .= '<Telefone>' . preg_replace('/[^0-9]/', '', $this->nfse->tomador_telefone) . '</Telefone>';
            }
            
            if ($this->nfse->tomador_email) {
                $xml .= '<Email>' . $this->nfse->tomador_email . '</Email>';
            }
            
            $xml .= '</Contato>';
        }
        
        $xml .= '</Tomador>';
        
        // Regime Especial de Tributação
        if ($this->nfse->regime_especial_tributacao) {
            $xml .= '<RegimeEspecialTributacao>' . $this->nfse->regime_especial_tributacao . '</RegimeEspecialTributacao>';
        }
        
        // Optante Simples Nacional
        $xml .= '<OptanteSimplesNacional>' . $this->nfse->optante_simples_nacional . '</OptanteSimplesNacional>';
        
        // Incentivador Cultural
        $xml .= '<IncentivoFiscal>' . $this->nfse->incentivador_cultural . '</IncentivoFiscal>';
        
        $xml .= '</InfDeclaracaoPrestacaoServico>';
        $xml .= '</Rps>';
        
        return $xml;
    }

    /**
     * Generate NFS-e XML (ABRASF 2.04)
     *
     * @return string
     */
    public function generateNFSeXML(): string
    {
        // For now, return RPS XML
        // In production, this would be the response from the city hall after RPS processing
        return $this->generateRPS();
    }

    /**
     * Transmit RPS to city hall
     *
     * @return array
     */
    public function transmit(): array
    {
        try {
            // Generate RPS XML
            $rpsXml = $this->generateRPS();
            
            // Save XML
            $this->nfse->xml = $rpsXml;
            $this->nfse->status = 'rps_gerado';
            $this->nfse->save();
            
            // TODO: Implement actual transmission to city hall
            // This would use SOAP/REST API depending on the city hall provider
            
            // For now, simulate successful transmission
            $this->nfse->status = 'autorizada';
            $this->nfse->numero_nfse = $this->getNextNFSeNumber();
            $this->nfse->codigo_verificacao = $this->generateVerificationCode();
            $this->nfse->protocolo = 'PROT' . time();
            $this->nfse->prefeitura_resposta = 'NFS-e autorizada com sucesso (SIMULAÇÃO)';
            $this->nfse->save();
            
            return [
                'success' => true,
                'message' => 'NFS-e autorizada com sucesso!',
                'numero_nfse' => $this->nfse->numero_nfse,
                'codigo_verificacao' => $this->nfse->codigo_verificacao,
            ];
            
        } catch (\Exception $e) {
            $this->nfse->status = 'erro';
            $this->nfse->prefeitura_resposta = $e->getMessage();
            $this->nfse->save();
            
            return [
                'success' => false,
                'message' => 'Erro ao transmitir NFS-e: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel NFS-e
     *
     * @param string $motivo
     * @return array
     */
    public function cancel(string $motivo): array
    {
        try {
            if (!$this->nfse->canBeCancelled()) {
                throw new \Exception('NFS-e não pode ser cancelada.');
            }
            
            // TODO: Implement actual cancellation with city hall
            
            // For now, simulate successful cancellation
            $this->nfse->status = 'cancelada';
            $this->nfse->data_cancelamento = now();
            $this->nfse->motivo_cancelamento = $motivo;
            $this->nfse->save();
            
            return [
                'success' => true,
                'message' => 'NFS-e cancelada com sucesso!',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao cancelar NFS-e: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query NFS-e status
     *
     * @return array
     */
    public function query(): array
    {
        try {
            // TODO: Implement actual query with city hall
            
            return [
                'success' => true,
                'status' => $this->nfse->status,
                'numero_nfse' => $this->nfse->numero_nfse,
                'codigo_verificacao' => $this->nfse->codigo_verificacao,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao consultar NFS-e: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate PDF
     *
     * @return string Path to PDF file
     */
    public function generatePDF(): string
    {
        // TODO: Implement PDF generation
        // For now, return empty string
        return '';
    }

    /**
     * Get next NFS-e number
     *
     * @return string
     */
    private function getNextNFSeNumber(): string
    {
        $lastNFSe = NFSe::where('workspace_id', $this->nfse->workspace_id)
            ->where('status', 'autorizada')
            ->orderBy('numero_nfse', 'desc')
            ->first();
        
        if ($lastNFSe && $lastNFSe->numero_nfse) {
            return (string)((int)$lastNFSe->numero_nfse + 1);
        }
        
        return '1';
    }

    /**
     * Generate verification code
     *
     * @return string
     */
    private function generateVerificationCode(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 9));
    }

    /**
     * Calculate ISS value
     *
     * @param float $valorServicos
     * @param float $aliquota
     * @param float $deducoes
     * @return float
     */
    public static function calculateISS(float $valorServicos, float $aliquota, float $deducoes = 0): float
    {
        $baseCalculo = $valorServicos - $deducoes;
        return $baseCalculo * ($aliquota / 100);
    }

    /**
     * Calculate base de cálculo
     *
     * @param float $valorServicos
     * @param float $deducoes
     * @return float
     */
    public static function calculateBaseCalculo(float $valorServicos, float $deducoes = 0): float
    {
        return $valorServicos - $deducoes;
    }

    /**
     * Calculate valor líquido
     *
     * @param float $valorServicos
     * @param float $deducoes
     * @param float $descontoIncondicionado
     * @param float $descontoCondicionado
     * @return float
     */
    public static function calculateValorLiquido(
        float $valorServicos,
        float $deducoes = 0,
        float $descontoIncondicionado = 0,
        float $descontoCondicionado = 0
    ): float {
        return $valorServicos - $deducoes - $descontoIncondicionado - $descontoCondicionado;
    }
}

