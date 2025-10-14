<?php

namespace Workdo\FiscalBR\Library;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\DA\NFe\Danfce;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Entities\SefazLog;

class NFCeService
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
            $this->tools->model('65'); // Modelo 65 = NFC-e
        } catch (\Exception $e) {
            throw new \Exception('Erro ao inicializar ferramentas SEFAZ: ' . $e->getMessage());
        }
    }

    /**
     * Generate NFC-e XML
     *
     * @param NFe $nfce
     * @return string
     */
    public function generateXML(NFe $nfce): string
    {
        try {
            $make = new Make();
            
            // Identificação da NFC-e
            $this->addIdentification($make, $nfce);
            
            // Emitente
            $this->addEmitter($make);
            
            // Destinatário (opcional para NFC-e)
            if ($nfce->destinatario_cpf_cnpj) {
                $this->addRecipient($make, $nfce);
            }
            
            // Produtos/Serviços
            foreach ($nfce->items as $index => $item) {
                $this->addProduct($make, $item, $index + 1);
            }
            
            // Totalizadores
            $this->addTotals($make, $nfce);
            
            // Transporte (não obrigatório para NFC-e)
            $this->addTransport($make);
            
            // Pagamento
            $this->addPayment($make, $nfce);
            
            // Informações Adicionais
            $this->addAdditionalInfo($make, $nfce);
            
            $xml = $make->getXML();
            $xml = $make->monta();
            
            return $xml;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar XML da NFC-e: ' . $e->getMessage());
        }
    }

    /**
     * Add identification section
     */
    private function addIdentification(Make $make, NFe $nfce)
    {
        $fiscalConfig = FiscalConfig::where('workspace_id', $this->workspaceId)->first();
        
        $std = new \stdClass();
        $std->cUF = $this->getUFCode($fiscalConfig->cnpj);
        $std->cNF = rand(10000000, 99999999);
        $std->natOp = 'VENDA';
        $std->mod = 65; // Modelo 65 = NFC-e
        $std->serie = (int)$nfce->serie;
        $std->nNF = (int)$nfce->numero;
        $std->dhEmi = $nfce->data_emissao->format('Y-m-d') . 'T' . $nfce->hora_emissao;
        $std->tpNF = 1; // 1=Saída
        $std->idDest = 1; // 1=Operação interna
        $std->cMunFG = '0000000'; // TODO: Obter do cadastro
        $std->tpImp = 4; // 4=DANFE NFC-e
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
        $std->CRT = $this->getCRT($fiscalConfig->regime_tributario);
        $std->CNPJ = preg_replace('/[^0-9]/', '', $fiscalConfig->cnpj);
        
        $make->tagemit($std);
        
        // TODO: Adicionar endereço do emitente
    }

    /**
     * Add recipient section (optional for NFC-e)
     */
    private function addRecipient(Make $make, NFe $nfce)
    {
        $std = new \stdClass();
        $std->xNome = $nfce->destinatario_nome;
        
        $cpfCnpj = preg_replace('/[^0-9]/', '', $nfce->destinatario_cpf_cnpj);
        if (strlen($cpfCnpj) === 11) {
            $std->CPF = $cpfCnpj;
        } else {
            $std->CNPJ = $cpfCnpj;
        }
        
        $std->indIEDest = 9; // 9=Não contribuinte
        
        $make->tagdest($std);
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
        
        // ICMS (Simples Nacional)
        $this->addICMSSN($make, $item, $itemNumber);
        
        // PIS
        $this->addPIS($make, $item, $itemNumber);
        
        // COFINS
        $this->addCOFINS($make, $item, $itemNumber);
    }

    /**
     * Add ICMS Simples Nacional
     */
    private function addICMSSN(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->orig = $item->icms_origem ?? 0;
        $std->CSOSN = $item->icms_csosn ?? '102'; // 102 = Tributada sem permissão de crédito
        
        $make->tagICMSSN($std);
    }

    /**
     * Add PIS tax
     */
    private function addPIS(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->CST = $item->pis_cst ?? '99';
        $std->vBC = 0.00;
        $std->pPIS = 0.00;
        $std->vPIS = 0.00;
        
        $make->tagPIS($std);
    }

    /**
     * Add COFINS tax
     */
    private function addCOFINS(Make $make, $item, int $itemNumber)
    {
        $std = new \stdClass();
        $std->item = $itemNumber;
        $std->CST = $item->cofins_cst ?? '99';
        $std->vBC = 0.00;
        $std->pCOFINS = 0.00;
        $std->vCOFINS = 0.00;
        
        $make->tagCOFINS($std);
    }

    /**
     * Add totals section
     */
    private function addTotals(Make $make, NFe $nfce)
    {
        $std = new \stdClass();
        $std->vBC = 0.00;
        $std->vICMS = 0.00;
        $std->vICMSDeson = 0.00;
        $std->vFCP = 0.00;
        $std->vBCST = 0.00;
        $std->vST = 0.00;
        $std->vFCPST = 0.00;
        $std->vFCPSTRet = 0.00;
        $std->vProd = $nfce->valor_produtos;
        $std->vFrete = 0.00;
        $std->vSeg = 0.00;
        $std->vDesc = $nfce->valor_desconto;
        $std->vII = 0.00;
        $std->vIPI = 0.00;
        $std->vIPIDevol = 0.00;
        $std->vPIS = 0.00;
        $std->vCOFINS = 0.00;
        $std->vOutro = 0.00;
        $std->vNF = $nfce->valor_total;
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
    private function addPayment(Make $make, NFe $nfce)
    {
        $std = new \stdClass();
        $std->vTroco = 0.00;
        
        $make->tagpag($std);
        
        // Payment details
        $std = new \stdClass();
        $std->tPag = '01'; // 01=Dinheiro (TODO: obter do POS)
        $std->vPag = $nfce->valor_total;
        
        $make->tagdetPag($std);
    }

    /**
     * Add additional info section
     */
    private function addAdditionalInfo(Make $make, NFe $nfce)
    {
        $std = new \stdClass();
        $std->infCpl = 'NFC-e emitida por Gestor Easy v3';
        
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
     * Transmit NFC-e to SEFAZ
     */
    public function transmit(string $signedXml, NFe $nfce): array
    {
        try {
            $response = $this->tools->sefazEnviaLote([$signedXml], 1);
            
            // Log the request
            $this->logSefazRequest('autorizacao_nfce', $signedXml, $response, $nfce->id);
            
            return [
                'success' => true,
                'response' => $response
            ];
        } catch (\Exception $e) {
            $this->logSefazRequest('autorizacao_nfce', $signedXml, $e->getMessage(), $nfce->id);
            
            throw new \Exception('Erro ao transmitir NFC-e: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR Code URL
     *
     * @param NFe $nfce
     * @return string
     */
    public function generateQRCodeURL(NFe $nfce): string
    {
        try {
            $chave = $nfce->chave_acesso;
            $fiscalConfig = FiscalConfig::where('workspace_id', $this->workspaceId)->first();
            
            // Get QR Code parameters
            $url = $this->getQRCodeBaseURL();
            $tpAmb = $this->config['tpAmb'];
            $cDest = ''; // CPF/CNPJ do destinatário (opcional)
            $dhEmi = $nfce->data_emissao->format('YmdHis');
            $vNF = number_format($nfce->valor_total, 2, '.', '');
            $vICMS = '0.00';
            $digVal = ''; // Digest Value do XML
            
            // CSC (Código de Segurança do Contribuinte)
            $csc = $fiscalConfig->csc ?? '';
            $idCSC = $fiscalConfig->csc_id ?? '1';
            
            // Generate QR Code string
            $qrString = "$chave|2|$tpAmb|$idCSC";
            $qrHash = hash('sha1', $qrString . $csc);
            
            $qrCodeURL = "$url?chNFe=$chave&nVersao=100&tpAmb=$tpAmb&cDest=$cDest&dhEmi=$dhEmi&vNF=$vNF&vICMS=$vICMS&digVal=$digVal&cIdToken=$idCSC&cHashQRCode=$qrHash";
            
            return $qrCodeURL;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar URL do QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Get QR Code base URL by UF
     */
    private function getQRCodeBaseURL(): string
    {
        $uf = $this->config['siglaUF'];
        $tpAmb = $this->config['tpAmb'];
        
        // URLs de consulta por UF (homologação e produção)
        $urls = [
            'AC' => $tpAmb == 1 ? 'https://www.sefaznet.ac.gov.br/nfce/qrcode' : 'https://hml.sefaznet.ac.gov.br/nfce/qrcode',
            'AL' => $tpAmb == 1 ? 'https://nfce.sefaz.al.gov.br/QRCode/consultarNFCe.jsp' : 'https://nfce.sefaz.al.gov.br/QRCode/consultarNFCe.jsp',
            'AM' => $tpAmb == 1 ? 'https://sistemas.sefaz.am.gov.br/nfceweb/consultarNFCe.jsp' : 'https://homnfce.sefaz.am.gov.br/nfceweb/consultarNFCe.jsp',
            'BA' => $tpAmb == 1 ? 'https://nfe.sefaz.ba.gov.br/servicos/nfce/qrcode.aspx' : 'https://hnfe.sefaz.ba.gov.br/servicos/nfce/qrcode.aspx',
            'CE' => $tpAmb == 1 ? 'https://nfce.sefaz.ce.gov.br/pages/ShowNFCe.html' : 'https://nfceh.sefaz.ce.gov.br/pages/ShowNFCe.html',
            'DF' => $tpAmb == 1 ? 'https://dec.fazenda.df.gov.br/ConsultarNFCe.aspx' : 'https://dec.fazenda.df.gov.br/ConsultarNFCe.aspx',
            'ES' => $tpAmb == 1 ? 'https://app.sefaz.es.gov.br/ConsultaNFCe' : 'https://homologacao.sefaz.es.gov.br/ConsultaNFCe',
            'GO' => $tpAmb == 1 ? 'https://nfe.go.gov.br/nfeweb/sites/nfce/danfeNFCe' : 'https://homolog.sefaz.go.gov.br/nfeweb/sites/nfce/danfeNFCe',
            'MA' => $tpAmb == 1 ? 'https://www.sefaz.ma.gov.br/portalnfce/qrcode' : 'https://www.hom.sefaz.ma.gov.br/portalnfce/qrcode',
            'MG' => $tpAmb == 1 ? 'https://portalsped.fazenda.mg.gov.br/portalnfce' : 'https://hnfce.fazenda.mg.gov.br/portalnfce',
            'MS' => $tpAmb == 1 ? 'https://www.dfe.ms.gov.br/nfce/qrcode' : 'https://www.dfe.ms.gov.br/nfce/qrcode',
            'MT' => $tpAmb == 1 ? 'https://www.sefaz.mt.gov.br/nfce/consultanfce' : 'https://homologacao.sefaz.mt.gov.br/nfce/consultanfce',
            'PA' => $tpAmb == 1 ? 'https://appnfc.sefa.pa.gov.br/portal/view/consultas/nfce/consultanfce.seam' : 'https://appnfc.sefa.pa.gov.br/portal-homologacao/view/consultas/nfce/consultanfce.seam',
            'PB' => $tpAmb == 1 ? 'https://www.receita.pb.gov.br/nfce/qrcode' : 'https://www.receita.pb.gov.br/nfcehom/qrcode',
            'PE' => $tpAmb == 1 ? 'https://nfce.sefaz.pe.gov.br/nfce-web/consultarNFCe' : 'https://nfcehomolog.sefaz.pe.gov.br/nfce-web/consultarNFCe',
            'PI' => $tpAmb == 1 ? 'https://www.sefaz.pi.gov.br/nfce/qrcode' : 'https://www.sefaz.pi.gov.br/nfcehom/qrcode',
            'PR' => $tpAmb == 1 ? 'https://www.fazenda.pr.gov.br/nfce/qrcode' : 'https://www.fazenda.pr.gov.br/nfcehom/qrcode',
            'RJ' => $tpAmb == 1 ? 'https://www4.fazenda.rj.gov.br/consultaNFCe/QRCode' : 'https://www4.fazenda.rj.gov.br/consultaNFCe/QRCode',
            'RN' => $tpAmb == 1 ? 'https://nfce.set.rn.gov.br/consultarNFCe.aspx' : 'https://hom.nfce.set.rn.gov.br/consultarNFCe.aspx',
            'RO' => $tpAmb == 1 ? 'https://www.nfce.sefin.ro.gov.br/consultanfce/consulta.jsp' : 'https://www.nfce.sefin.ro.gov.br/consultanfce/consulta.jsp',
            'RR' => $tpAmb == 1 ? 'https://www.sefaz.rr.gov.br/nfce/servlet/qrcode' : 'https://homologacao.sefaz.rr.gov.br/nfce/servlet/qrcode',
            'RS' => $tpAmb == 1 ? 'https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx' : 'https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx',
            'SC' => $tpAmb == 1 ? 'https://sat.sef.sc.gov.br/nfce/consulta' : 'https://hom.sat.sef.sc.gov.br/nfce/consulta',
            'SE' => $tpAmb == 1 ? 'https://www.nfce.se.gov.br/portal/consultarNFCe.jsp' : 'https://www.hom.nfe.se.gov.br/portal/consultarNFCe.jsp',
            'SP' => $tpAmb == 1 ? 'https://www.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaQRCode.aspx' : 'https://www.homologacao.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaQRCode.aspx',
            'TO' => $tpAmb == 1 ? 'https://www.sefaz.to.gov.br/nfce/qrcode' : 'https://homologacao.sefaz.to.gov.br/nfce/qrcode',
        ];
        
        return $urls[$uf] ?? $urls['SP'];
    }

    /**
     * Generate DANFCE (Cupom) PDF
     */
    public function generateDANFCE(string $xml): string
    {
        try {
            $danfce = new Danfce($xml);
            $pdf = $danfce->render();
            
            return $pdf;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar DANFCE: ' . $e->getMessage());
        }
    }

    /**
     * Log SEFAZ request
     */
    private function logSefazRequest(string $operation, string $request, string $response, ?int $nfceId = null)
    {
        SefazLog::create([
            'workspace_id' => $this->workspaceId,
            'nfe_id' => $nfceId,
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

