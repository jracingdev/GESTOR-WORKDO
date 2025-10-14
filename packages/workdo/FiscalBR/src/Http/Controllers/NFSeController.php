<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFSe;
use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Library\NFSeService;

class NFSeController extends Controller
{
    /**
     * Display a listing of NFS-e.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $nfses = NFSe::where('workspace_id', $workspaceId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('fiscalbr::nfse.index', compact('nfses'));
    }

    /**
     * Show the form for creating a new NFS-e.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $workspaceId = getActiveWorkSpace();
        $fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->first();
        
        if (!$fiscalConfig) {
            return redirect()->route('fiscalbr.config.index')
                ->with('error', 'Configure os dados fiscais antes de emitir NFS-e.');
        }
        
        return view('fiscalbr::nfse.create', compact('fiscalConfig'));
    }

    /**
     * Store a newly created NFS-e in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'tomador_nome' => 'required|string|max:255',
            'tomador_cpf_cnpj' => 'required|string|max:18',
            'descricao_servico' => 'required|string',
            'item_lista_servico' => 'required|string|max:10',
            'valor_servicos' => 'required|numeric|min:0',
            'aliquota_iss' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $workspaceId = getActiveWorkSpace();
            $fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->firstOrFail();
            
            // Calculate values
            $valorServicos = (float)$request->valor_servicos;
            $aliquotaIss = (float)$request->aliquota_iss;
            $deducoes = (float)($request->valor_deducoes ?? 0);
            
            $baseCalculo = NFSeService::calculateBaseCalculo($valorServicos, $deducoes);
            $valorIss = NFSeService::calculateISS($valorServicos, $aliquotaIss, $deducoes);
            $valorLiquido = NFSeService::calculateValorLiquido(
                $valorServicos,
                $deducoes,
                (float)($request->desconto_incondicionado ?? 0),
                (float)($request->desconto_condicionado ?? 0)
            );
            
            // Get next RPS number
            $nextRpsNumber = $fiscalConfig->nfse_ultimo_numero_rps + 1;
            
            // Create NFS-e
            $nfse = NFSe::create([
                'workspace_id' => $workspaceId,
                'numero_rps' => $nextRpsNumber,
                'serie_rps' => $fiscalConfig->nfse_serie_rps,
                'data_emissao' => $request->data_emissao ?? now(),
                'status' => 'rascunho',
                
                // Tomador
                'tomador_nome' => $request->tomador_nome,
                'tomador_cpf_cnpj' => $request->tomador_cpf_cnpj,
                'tomador_inscricao_municipal' => $request->tomador_inscricao_municipal,
                'tomador_endereco' => $request->tomador_endereco,
                'tomador_numero' => $request->tomador_numero,
                'tomador_complemento' => $request->tomador_complemento,
                'tomador_bairro' => $request->tomador_bairro,
                'tomador_cidade' => $request->tomador_cidade,
                'tomador_uf' => $request->tomador_uf,
                'tomador_cep' => $request->tomador_cep,
                'tomador_email' => $request->tomador_email,
                'tomador_telefone' => $request->tomador_telefone,
                
                // Serviço
                'descricao_servico' => $request->descricao_servico,
                'codigo_servico' => $request->codigo_servico ?? '',
                'codigo_cnae' => $request->codigo_cnae ?? $fiscalConfig->nfse_codigo_cnae,
                'item_lista_servico' => $request->item_lista_servico,
                'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio,
                
                // Valores
                'valor_servicos' => $valorServicos,
                'valor_deducoes' => $deducoes,
                'valor_pis' => (float)($request->valor_pis ?? 0),
                'valor_cofins' => (float)($request->valor_cofins ?? 0),
                'valor_inss' => (float)($request->valor_inss ?? 0),
                'valor_ir' => (float)($request->valor_ir ?? 0),
                'valor_csll' => (float)($request->valor_csll ?? 0),
                'valor_iss' => $valorIss,
                'valor_iss_retido' => (float)($request->valor_iss_retido ?? 0),
                'valor_outras_retencoes' => (float)($request->valor_outras_retencoes ?? 0),
                'base_calculo' => $baseCalculo,
                'aliquota_iss' => $aliquotaIss,
                'valor_liquido' => $valorLiquido,
                'desconto_incondicionado' => (float)($request->desconto_incondicionado ?? 0),
                'desconto_condicionado' => (float)($request->desconto_condicionado ?? 0),
                
                // ISS
                'iss_retido' => $request->iss_retido ?? 'nao',
                'exigibilidade_iss' => $request->exigibilidade_iss ?? '1',
                'municipio_prestacao' => $fiscalConfig->codigo_municipio,
                'municipio_incidencia' => $request->municipio_incidencia,
                
                // Regime
                'regime_especial_tributacao' => $request->regime_especial_tributacao ?? $fiscalConfig->nfse_regime_especial,
                'optante_simples_nacional' => $request->optante_simples_nacional ?? $fiscalConfig->nfse_optante_simples,
                'incentivador_cultural' => $request->incentivador_cultural ?? $fiscalConfig->nfse_incentivador_cultural,
                'natureza_operacao' => $request->natureza_operacao ?? '1',
                
                // Prefeitura
                'prefeitura_provedor' => $fiscalConfig->nfse_provedor,
                'prefeitura_versao' => $fiscalConfig->nfse_versao,
            ]);
            
            // Update last RPS number
            $fiscalConfig->nfse_ultimo_numero_rps = $nextRpsNumber;
            $fiscalConfig->save();
            
            return redirect()->route('fiscalbr.nfse.show', $nfse->id)
                ->with('success', 'NFS-e criada com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar NFS-e: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified NFS-e.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);
        
        return view('fiscalbr::nfse.show', compact('nfse'));
    }

    /**
     * Transmit NFS-e.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function transmitir($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);
            
            $service = new NFSeService($nfse);
            $result = $service->transmit();
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel NFS-e.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|min:15',
        ]);

        try {
            $workspaceId = getActiveWorkSpace();
            $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);
            
            $service = new NFSeService($nfse);
            $result = $service->cancel($request->motivo);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query NFS-e status.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function consultar($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);
            
            $service = new NFSeService($nfse);
            $result = $service->query();
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download NFS-e XML.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadXml($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$nfse->xml) {
            return redirect()->back()->with('error', 'XML não disponível.');
        }

        $filename = sprintf('NFSE_%s_%s.xml', $nfse->numero_nfse ?? $nfse->numero_rps, $nfse->data_emissao->format('Ymd'));

        return response($nfse->xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download NFS-e PDF.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function pdf($id)
    {
        $workspaceId = getActiveWorkSpace();
        $nfse = NFSe::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$nfse->isAutorizada()) {
            return redirect()->back()->with('error', 'NFS-e não autorizada.');
        }

        // TODO: Generate PDF
        return redirect()->back()->with('info', 'Geração de PDF em desenvolvimento.');
    }
}

