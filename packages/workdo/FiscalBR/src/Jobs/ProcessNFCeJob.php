<?php

namespace Workdo\FiscalBR\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Library\NFCeService;

class ProcessNFCeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nfceId;
    protected $workspaceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $nfceId, int $workspaceId)
    {
        $this->nfceId = $nfceId;
        $this->workspaceId = $workspaceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $nfce = NFe::with('items')->findOrFail($this->nfceId);
            
            if ($nfce->status !== 'rascunho' && $nfce->status !== 'processando') {
                return;
            }

            // Update status to processing
            $nfce->update(['status' => 'processando']);

            $nfceService = new NFCeService($this->workspaceId);

            // Generate XML
            $xml = $nfceService->generateXML($nfce);
            
            // Sign XML
            $signedXml = $nfceService->signXML($xml);
            
            // Save signed XML
            $nfce->update(['xml_enviado' => $signedXml]);

            // Transmit to SEFAZ
            $result = $nfceService->transmit($signedXml, $nfce);

            // Process response
            $this->processResponse($nfce, $result['response'], $nfceService);

        } catch (\Exception $e) {
            $nfce->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process SEFAZ response
     */
    private function processResponse(NFe $nfce, string $response, NFCeService $nfceService): void
    {
        // Parse XML response
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            throw new \Exception('Resposta inválida da SEFAZ');
        }

        $cStat = (string)$xml->protNFe->infProt->cStat ?? (string)$xml->cStat;

        if ($cStat === '100') {
            // Authorized
            $chave = (string)$xml->protNFe->infProt->chNFe;
            
            // Generate QR Code URL
            $qrCodeURL = $nfceService->generateQRCodeURL($nfce);
            
            $nfce->update([
                'status' => 'autorizada',
                'protocolo' => (string)$xml->protNFe->infProt->nProt,
                'chave_acesso' => $chave,
                'data_autorizacao' => now(),
                'xml_autorizado' => $response,
                'qr_code_url' => $qrCodeURL,
            ]);
        } else {
            // Rejected
            $nfce->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => (string)$xml->xMotivo ?? 'Erro desconhecido'
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $nfce = NFe::find($this->nfceId);
        
        if ($nfce) {
            $nfce->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => 'Erro no processamento: ' . $exception->getMessage()
            ]);
        }
    }
}

