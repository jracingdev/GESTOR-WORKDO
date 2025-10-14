<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Jobs\ProcessNFCeJob;
use Workdo\FiscalBR\Library\NFCeService;

class NFCeController extends Controller
{
    /**
     * Display a listing of NFC-e.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $nfces = NFe::where('workspace_id', $workspaceId)
            ->where('tipo', 'nfce')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('fiscalbr::nfce.index', compact('nfces'));
    }

    /**
     * Show the form for creating a new NFC-e.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('fiscalbr::nfce.create');
    }

    /**
     * Store a newly created NFC-e in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $workspaceId = getActiveWorkSpace();
        $fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->first();

        if (!$fiscalConfig) {
            return redirect()->back()->with('error', 'Configure os dados fiscais antes de emitir NFC-e.');
        }

        $nfce = NFe::create([
            'workspace_id' => $workspaceId,
            'tipo' => 'nfce',
            'numero' => $fiscalConfig->getNextNFCeNumber(),
            'serie' => $fiscalConfig->serie_nfce ?? '1',
            'modelo' => '65',
            'data_emissao' => now()->toDateString(),
            'hora_emissao' => now()->toTimeString(),
            'destinatario_cpf_cnpj' => $request->destinatario_cpf_cnpj ?? null,
            'destinatario_nome' => $request->destinatario_nome ?? 'CONSUMIDOR',
            'status' => 'rascunho',
        ]);

        return redirect()->route('fiscalbr.nfce.show', $nfce->id)->with('success', 'NFC-e criada como rascunho!');
    }

    /**
     * Display the specified NFC-e.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfce = NFe::with('items')->where('workspace_id', $workspaceId)->findOrFail($id);
        
        return view('fiscalbr::nfce.show', compact('nfce'));
    }

    /**
     * Transmit NFC-e to SEFAZ.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function transmitir($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfce = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

            if ($nfce->status !== 'rascunho') {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas NFC-e em rascunho podem ser transmitidas.'
                ], 400);
            }

            // Dispatch job to process NFC-e
            ProcessNFCeJob::dispatch($nfce->id, $workspaceId);

            return response()->json([
                'success' => true,
                'message' => 'NFC-e enviada para processamento. Aguarde...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate cupom (DANFCE) PDF.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cupom($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfce = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

            if (!$nfce->xml_autorizado) {
                return redirect()->back()->with('error', 'NFC-e não autorizada.');
            }

            $nfceService = new NFCeService($workspaceId);
            $pdf = $nfceService->generateDANFCE($nfce->xml_autorizado);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="CUPOM_' . $nfce->numero . '.pdf"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download NFC-e XML.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadXml($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfce = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$nfce->xml_autorizado) {
            return redirect()->back()->with('error', 'NFC-e não autorizada.');
        }

        return response($nfce->xml_autorizado)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="NFCe_' . $nfce->chave_acesso . '.xml"');
    }

    /**
     * Display QR Code for NFC-e.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function qrcode($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfce = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$nfce->qr_code_url) {
            return redirect()->back()->with('error', 'QR Code não disponível.');
        }

        return view('fiscalbr::nfce.qrcode', compact('nfce'));
    }
}

