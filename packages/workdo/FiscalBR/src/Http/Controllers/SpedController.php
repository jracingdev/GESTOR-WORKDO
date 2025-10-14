<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\Sped;
use Workdo\FiscalBR\Library\SpedFiscalService;
use Carbon\Carbon;

class SpedController extends Controller
{
    /**
     * Display a listing of SPED files.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $speds = Sped::where('workspace_id', $workspaceId)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate(20);
        
        return view('fiscalbr::sped.index', compact('speds'));
    }

    /**
     * Show the form for creating a new SPED file.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $anos = range(date('Y'), date('Y') - 5);
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        return view('fiscalbr::sped.create', compact('anos', 'meses'));
    }

    /**
     * Generate SPED file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'ano' => 'required|integer|min:2000|max:' . date('Y'),
            'mes' => 'required|integer|min:1|max:12',
            'perfil' => 'required|in:A,B,C',
        ]);

        try {
            $workspaceId = getActiveWorkSpace();
            $ano = $request->ano;
            $mes = $request->mes;

            // Check if already exists
            $existing = Sped::where('workspace_id', $workspaceId)
                ->where('ano', $ano)
                ->where('mes', $mes)
                ->where('tipo', 'EFD_ICMS_IPI')
                ->first();

            if ($existing) {
                return redirect()->back()->with('error', 'SPED já gerado para este período.');
            }

            // Create SPED record
            $sped = Sped::create([
                'workspace_id' => $workspaceId,
                'ano' => $ano,
                'mes' => $mes,
                'tipo' => 'EFD_ICMS_IPI',
                'perfil' => $request->perfil,
                'status' => 'gerando',
            ]);

            // Generate SPED file
            $spedService = new SpedFiscalService($workspaceId, $ano, $mes);
            $arquivo = $spedService->generate();

            // Save file
            $nomeArquivo = sprintf('SPED_FISCAL_%04d%02d.txt', $ano, $mes);
            
            $sped->update([
                'arquivo' => $arquivo,
                'nome_arquivo' => $nomeArquivo,
                'status' => 'gerado',
                'data_geracao' => now(),
            ]);

            return redirect()->route('fiscalbr.sped.show', $sped->id)
                ->with('success', 'SPED Fiscal gerado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao gerar SPED: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified SPED file.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $workspaceId = getActiveWorkSpace();
        $sped = Sped::where('workspace_id', $workspaceId)->findOrFail($id);
        
        // Count lines
        $totalLinhas = substr_count($sped->arquivo, "\r\n") + 1;
        
        return view('fiscalbr::sped.show', compact('sped', 'totalLinhas'));
    }

    /**
     * Download SPED file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        $workspaceId = getActiveWorkSpace();
        $sped = Sped::where('workspace_id', $workspaceId)->findOrFail($id);

        if (!$sped->arquivo) {
            return redirect()->back()->with('error', 'Arquivo SPED não disponível.');
        }

        return response($sped->arquivo)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $sped->nome_arquivo . '"');
    }

    /**
     * Delete SPED file.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $workspaceId = getActiveWorkSpace();
        $sped = Sped::where('workspace_id', $workspaceId)->findOrFail($id);

        $sped->delete();

        return redirect()->route('fiscalbr.sped.index')
            ->with('success', 'SPED excluído com sucesso!');
    }

    /**
     * Send SPED to accounting.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviarContabilidade($id)
    {
        try {
            $workspaceId = getActiveWorkSpace();
            $sped = Sped::where('workspace_id', $workspaceId)->findOrFail($id);

            // TODO: Implement sending to accounting
            // This could be via email, API, FTP, etc.

            return response()->json([
                'success' => true,
                'message' => 'SPED enviado para contabilidade com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

