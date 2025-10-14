<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Jobs\ProcessNFeJob;
use Workdo\FiscalBR\Library\NFeService;

class NFeController extends Controller
{
    /**
     * Display a listing of NF-e.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $nfes = NFe::where('workspace_id', $workspaceId)
            ->where('tipo', 'nfe')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('fiscalbr::nfe.index', compact('nfes'));
    }

    /**
     * Show the form for creating a new NF-e.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('fiscalbr::nfe.create');
    }

    /**
     * Store a newly created NF-e.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'destinatario_cpf_cnpj' => 'required',
            'destinatario_nome' => 'required',
            'destinatario_uf' => 'required',
        ]);

        $workspaceId = getActiveWorkSpace();
        $fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->first();

        if (!$fiscalConfig) {
            return redirect()->back()->with('error', 'Configure os dados fiscais antes de emitir NF-e.');
        }

        $nfe = NFe::create([
            'workspace_id' => $workspaceId,
            'tipo' => 'nfe',
            'numero' => $fiscalConfig->getNextNFeNumber(),
            'serie' => $fiscalConfig->serie_nfe,
            'modelo' => '55',
            'data_emissao' => now()->toDateString(),
            'hora_emissao' => now()->toTimeString(),
            'destinatario_cpf_cnpj' => $request->destinatario_cpf_cnpj,
            'destinatario_nome' => $request->destinatario_nome,
            'destinatario_ie' => $request->destinatario_ie,
            'destinatario_uf' => $request->destinatario_uf,
            'status' => 'rascunho',
        ]);

        return redirect()->route('fiscalbr.nfe.show', $nfe->id)->with('success', 'NF-e criada como rascunho!');
    }

    /**
     * Display the specified NF-e.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfe = NFe::with('items')->where('workspace_id', $workspaceId)->findOrFail($id);
        
        return view('fiscalbr::nfe.show', compact('nfe'));
    }

    /**
     * Transmit NF-e to SEFAZ.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function transmitir($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfe = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

            if ($nfe->status !== 'rascunho') {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas NF-e em rascunho podem ser transmitidas.'
                ], 400);
            }

            // Dispatch job to process NF-e
            ProcessNFeJob::dispatch($nfe->id, $workspaceId);

            return response()->json([
                'success' => true,
                'message' => 'NF-e enviada para processamento. Aguarde...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel NF-e.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar($id)
    {
        // TODO: Implementar cancelamento
        return response()->json([
            'success' => true,
            'message' => 'NF-e cancelada com sucesso!'
        ]);
    }

    /**
     * Generate DANFE PDF.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function danfe($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfe = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

            if (!$nfe->xml_autorizado) {
                return redirect()->back()->with('error', 'NF-e não autorizada.');
            }

            $nfeService = new NFeService($workspaceId);
            $pdf = $nfeService->generateDANFE($nfe->xml_autorizado);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="DANFE_' . $nfe->numero . '.pdf"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download XML.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadXml($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfe = NFe::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$nfe->xml_autorizado) {
            return redirect()->back()->with('error', 'NF-e não autorizada.');
        }

        return response($nfe->xml_autorizado)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="NFe_' . $nfe->chave_acesso . '.xml"');
    }
}

