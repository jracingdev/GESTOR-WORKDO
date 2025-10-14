<?php

namespace Workdo\FiscalBR\Library;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\DA\NFe\Danfe;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Entities\SefazLog;

class NFeService
{
    protected $workspaceId;
    protected $tools;
    protected $config;

    public function __construct(int $workspaceId)
    {
        $this->workspaceId = $workspaceId;
        $this->config = SefazConfigService::getConfig($workspaceId);
        $this->initializeTools();
    }

    /**
     * Initialize NFePHP Tools
     */
    private function initializeTools()
    {
        try {
            $certificateData = SefazConfigService::getCertificate($this->workspaceId);
            $certificate = Certificate::readPfx($certificateData['content'], $certificateData['password']);
            
            $this->tools = new Tools(json_encode($this->config), $certificate);
            $this->tools->model('55'); // Modelo 55 = NF-e
        } catch (\Exception $e) {
            throw new \Exception('Erro ao inicializar ferramentas SEFAZ: ' . $e->getMessage());
        }
    }

    /**
     * Generate NF-e XML
     *
     * @param NFe $nfe
     * @return string
     */
    public function generateXML(NFe $nfe): string
    {
        try {
            $make = new Make();
            
            // Identificação da NF-e
            $this->addIdentification($make, $nfe);
            
            // Emitente
            $this->addEmitter($make);
            
            // Destinatário
            $this->addRecipient($make, $nfe);
            
            // Produtos/Serviços
            foreach ($nfe->items as $index => $item) {
                $this->addProduct($make, $item, $index + 1);
            }
            
            // Totalizadores
            $this->addTotals($make, $nfe);
            
            // Transporte
            $this->addTransport($make);
            
            // Pagamento
            $this->addPayment($make, $nfe);
            
            // Informações Adicionais
            $this->addAdditionalInfo($make, $nfe);
            
            $xml = $make->getXML();
            $xml = $make->monta();
            
            return $xml;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar XML da NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Add identification section
     */
    private function addIdentification(Make $make, NFe $nfe)
    {
        $fiscalConfig = FiscalConfig::where('workspace_id', $this->workspaceId)->first();
        
        $std = new \stdClass();
        $std->cUF = $this->getUFCode($fiscalConfig->cnpj);
        $std->cNF = rand(10000000, 99999999);
        $std->natOp = 'VENDA';
        $std->mod = 55;
        $std->serie = (int)$nfe->serie;
        $std->nNF = (int)$nfe->numero;
        $std->dhEmi = $nfe->data_emissao->format('Y-m-d') . 'T' . $nfe->hora_emissao;
        $std->dhSaiEnt = $nfe->data_emissao->format('Y-m-d') . 'T' . $nfe->hora_emissao;
        $std->tpNF = 1; // 1=Saída
        $std->idDest = $this->getIdDest($nfe);
        $std->cMunFG = '0000000'; // TODO: Obter do cadastro
        $std->tpImp = 1; // 1=DANFE normal
        $std->tpEmis = 1; // 1=Normal
        $std->cDV = 0; // Será calculado
        $std->tpAmb = $this->config['tpAmb'];
        $std->finNFe = 1; // 1=Normal
        $std->indFinal = 1; // 1=Consumidor final
        $std->indPres = 1; // 1=Presencial
        $std->procEmi = 0; // 0=Aplicativo do contribuinte
        $std->verProc = '1.0.0';
        
        $make->taginfNFe($std);
    }

    /**
     * Add emitter section
     */
    private function addEmitter(Make $make)
    {
        $fiscalConfig = FiscalConfig::where('workspace_id', $this->workspaceId)->first();
        
        $std = new \stdClass();
        $std->xNome = $fiscalConfig->razao_social;
        $std->xFant = $fiscalConfig->nome_fantasia ?? $fiscalConfig->razao_social;
        $std->IE = preg_replace('/[^0-9]/', '', $fiscalConfig->inscricao_estadual ?? '');
        $std->CNAE = preg_replace('/[^0-9]/', '', $fiscalConfig->cnae ?? '');
        $std->CRT = $this->getCRT($fiscalConfig->regime_tributario);
        $std->CNPJ = preg_replace('/[^0-9]/', '', $fiscalConfig->cnpj);
        
        $make->tagemit($std);
        
        // TODO: Adicionar endereço do emitente
    }

    /**
     * Add recipient section
     */
    private function addRecipient(Make $make, NFe $nfe)
    {
        $std = new \stdClass();
        $std->xNome = $nfe->destinatario_nome;
        
        $cpfCnpj = preg_replace('/[^0-9]/', '', $nfe->destinatario_cpf_cnpj);
        if (strlen($cpfCnpj) === 11) {
            $std->CPF = $cpfCnpj;
        } else {
            $std->CNPJ = $cpfCnpj;
        }
        
        if ($nfe->destinatario_ie) {
            $std->IE = preg_replace('/[^0-9]/', '', $nfe->destinatario_ie);
        }
        
        $std->indIEDest = 9; // 9=Não contribuinte
        
        $make->tagdest($std);
        
        // TODO: Adicionar endereço do destinatário
    }

    /**
     * Add product section
     */
    private function addProduct(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->cProd = $item->codigo_produto;
        $std->cEAN = 'SEM GTIN';
        $std->xProd = $item->descricao;
        $std->NCM = $item->ncm;
        $std->CFOP = $item->cfop;
        $std->uCom = $item->unidade;
        $std->qCom = $item->quantidade;
        $std->vUnCom = $item->valor_unitario;
        $std->vProd = $item->valor_total;
        $std->cEANTrib = 'SEM GTIN';
        $std->uTrib = $item->unidade;
        $std->qTrib = $item->quantidade;
        $std->vUnTrib = $item->valor_unitario;
        $std->indTot = 1;
        
        $make->tagprod($std);
        
        // Impostos
        $make->tagimposto($std);
        
        // ICMS
        $this->addICMS($make, $item, $itemNumber);
        
        // IPI
        if ($item->ipi_valor > 0) {
            $this->addIPI($make, $item, $itemNumber);
        }
        
        // PIS
        $this->addPIS($make, $item, $itemNumber);
        
        // COFINS
        $this->addCOFINS($make, $item, $itemNumber);
    }

    /**
     * Add ICMS tax
     */
    private function addICMS(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->orig = $item->icms_origem;
        $std->CST = $item->icms_cst;
        $std->modBC = 3;
        $std->vBC = $item->icms_base_calculo;
        $std->pICMS = $item->icms_aliquota;
        $std->vICMS = $item->icms_valor;
        
        $make->tagICMS($std);
    }

    /**
     * Add IPI tax
     */
    private function addIPI(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->CST = $item->ipi_cst;
        $std->vBC = $item->ipi_base_calculo;
        $std->pIPI = $item->ipi_aliquota;
        $std->vIPI = $item->ipi_valor;
        
        $make->tagIPI($std);
    }

    /**
     * Add PIS tax
     */
    private function addPIS(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->CST = $item->pis_cst;
        $std->vBC = $item->pis_base_calculo;
        $std->pPIS = $item->pis_aliquota;
        $std->vPIS = $item->pis_valor;
        
        $make->tagPIS($std);
    }

    /**
     * Add COFINS tax
     */
    private function addCOFINS(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->CST = $item->cofins_cst;
        $std->vBC = $item->cofins_base_calculo;
        $std->pCOFINS = $item->cofins_aliquota;
        $std->vCOFINS = $item->cofins_valor;
        
        $make->tagCOFINS($std);
    }

    /**
     * Add totals section
     */
    private function addTotals(Make $make, NFe $nfe)
    {
        $std = new \stdClass();
        $std->vBC = $nfe->items->sum('icms_base_calculo');
        $std->vICMS = $nfe->valor_icms;
        $std->vICMSDeson = 0.00;
        $std->vFCP = 0.00;
        $std->vBCST = 0.00;
        $std->vST = 0.00;
        $std->vFCPST = 0.00;
        $std->vFCPSTRet = 0.00;
        $std->vProd = $nfe->valor_produtos;
        $std->vFrete = $nfe->valor_frete;
        $std->vSeg = 0.00;
        $std->vDesc = $nfe->valor_desconto;
        $std->vII = 0.00;
        $std->vIPI = $nfe->valor_ipi;
        $std->vIPIDevol = 0.00;
        $std->vPIS = $nfe->valor_pis;
        $std->vCOFINS = $nfe->valor_cofins;
        $std->vOutro = 0.00;
        $std->vNF = $nfe->valor_total;
        $std->vTotTrib = 0.00;
        
        $make->tagICMSTot($std);
    }

    /**
     * Add transport section
     */
    private function addTransport(Make $make)
    {
        $std = new \stdClass();
        $std->modFrete = 9; // 9=Sem frete
        
        $make->tagtransp($std);
    }

    /**
     * Add payment section
     */
    private function addPayment(Make $make, NFe $nfe)
    {
        $std = new \stdClass();
        $std->vTroco = 0.00;
        
        $make->tagpag($std);
        
        $std = new \stdClass();
        $std->tPag = '01'; // 01=Dinheiro
        $std->vPag = $nfe->valor_total;
        
        $make->tagdetPag($std);
    }

    /**
     * Add additional info section
     */
    private function addAdditionalInfo(Make $make, NFe $nfe)
    {
        $std = new \stdClass();
        $std->infCpl = 'Documento emitido por Gestor Easy v3';
        
        $make->taginfAdic($std);
    }

    /**
     * Sign XML
     */
    public function signXML(string $xml): string
    {
        try {
            return $this->tools->signNFe($xml);
        } catch (\Exception $e) {
            throw new \Exception('Erro ao assinar XML: ' . $e->getMessage());
        }
    }

    /**
     * Transmit NF-e to SEFAZ
     */
    public function transmit(string $signedXml, NFe $nfe): array
    {
        try {
            $response = $this->tools->sefazEnviaLote([$signedXml], 1);
            
            // Log the request
            $this->logSefazRequest('autorizacao', $signedXml, $response, $nfe->id);
            
            return [
                'success' => true,
                'response' => $response
            ];
        } catch (\Exception $e) {
            $this->logSefazRequest('autorizacao', $signedXml, $e->getMessage(), $nfe->id);
            
            throw new \Exception('Erro ao transmitir NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Generate DANFE PDF
     */
    public function generateDANFE(string $xml): string
    {
        try {
            $danfe = new Danfe($xml);
            $pdf = $danfe->render();
            
            return $pdf;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar DANFE: ' . $e->getMessage());
        }
    }

    /**
     * Log SEFAZ request
     */
    private function logSefazRequest(string $operation, string $request, string $response, ?int $nfeId = null)
    {
        SefazLog::create([
            'workspace_id' => $this->workspaceId,
            'nfe_id' => $nfeId,
            'operacao' => $operation,
            'uf' => $this->config['siglaUF'],
            'ambiente' => $this->config['tpAmb'] == 1 ? 'producao' : 'homologacao',
            'request' => $request,
            'response' => $response,
            'status_code' => null,
            'mensagem' => null,
            'tempo_resposta' => null
        ]);
    }

    /**
     * Helper methods
     */
    private function getUFCode(string $cnpj): int
    {
        $ufCodes = [
            'RO' => 11, 'AC' => 12, 'AM' => 13, 'RR' => 14, 'PA' => 15,
            'AP' => 16, 'TO' => 17, 'MA' => 21, 'PI' => 22, 'CE' => 23,
            'RN' => 24, 'PB' => 25, 'PE' => 26, 'AL' => 27, 'SE' => 28,
            'BA' => 29, 'MG' => 31, 'ES' => 32, 'RJ' => 33, 'SP' => 35,
            'PR' => 41, 'SC' => 42, 'RS' => 43, 'MS' => 50, 'MT' => 51,
            'GO' => 52, 'DF' => 53
        ];
        
        $uf = SefazConfigService::getUFFromCNPJ($cnpj);
        return $ufCodes[$uf] ?? 35;
    }

    private function getIdDest(NFe $nfe): int
    {
        // 1=Operação interna, 2=Operação interestadual, 3=Operação com exterior
        return 1; // TODO: Implementar lógica
    }

    private function getCRT(string $regimeTributario): int
    {
        return match($regimeTributario) {
            'simples_nacional' => 1,
            'lucro_presumido' => 3,
            'lucro_real' => 3,
            default => 1
        };
    }
}

