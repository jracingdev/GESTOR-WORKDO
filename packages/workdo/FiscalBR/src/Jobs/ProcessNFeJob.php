<?php

namespace Workdo\FiscalBR\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Library\NFeService;

class ProcessNFeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nfeId;
    protected $workspaceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $nfeId, int $workspaceId)
    {
        $this->nfeId = $nfeId;
        $this->workspaceId = $workspaceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $nfe = NFe::with('items')->findOrFail($this->nfeId);
            
            if ($nfe->status !== 'rascunho' && $nfe->status !== 'processando') {
                return;
            }

            // Update status to processing
            $nfe->update(['status' => 'processando']);

            $nfeService = new NFeService($this->workspaceId);

            // Generate XML
            $xml = $nfeService->generateXML($nfe);
            
            // Sign XML
            $signedXml = $nfeService->signXML($xml);
            
            // Save signed XML
            $nfe->update(['xml_enviado' => $signedXml]);

            // Transmit to SEFAZ
            $result = $nfeService->transmit($signedXml, $nfe);

            // Process response
            $this->processResponse($nfe, $result['response']);

        } catch (\Exception $e) {
            $nfe->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process SEFAZ response
     */
    private function processResponse(NFe $nfe, string $response): void
    {
        // Parse XML response
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            throw new \Exception('Resposta inválida da SEFAZ');
        }

        $cStat = (string)$xml->protNFe->infProt->cStat ?? (string)$xml->cStat;

        if ($cStat === '100') {
            // Authorized
            $nfe->update([
                'status' => 'autorizada',
                'protocolo' => (string)$xml->protNFe->infProt->nProt,
                'chave_acesso' => (string)$xml->protNFe->infProt->chNFe,
                'data_autorizacao' => now(),
                'xml_autorizado' => $response
            ]);
        } else {
            // Rejected
            $nfe->update([
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
        $nfe = NFe::find($this->nfeId);
        
        if ($nfe) {
            $nfe->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => 'Erro no processamento: ' . $exception->getMessage()
            ]);
        }
    }
}

