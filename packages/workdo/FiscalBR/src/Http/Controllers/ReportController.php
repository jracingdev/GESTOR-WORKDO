<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\Sped;
use Workdo\FiscalBR\Entities\NFSe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('fiscalbr::reports.index');
    }

    /**
     * Generate NF-e report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function nfe(Request $request)
    {
        $data_inicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $data_fim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $query = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('modelo', '55')
            ->whereBetween('created_at', [$data_inicio, $data_fim]);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $notas = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas
        $total_notas = $notas->count();
        $valor_total = $notas->sum('valor_total');
        $valor_icms = $notas->sum('valor_icms');
        $valor_pis = $notas->sum('valor_pis');
        $valor_cofins = $notas->sum('valor_cofins');

        return view('fiscalbr::reports.nfe', compact(
            'notas',
            'data_inicio',
            'data_fim',
            'status',
            'total_notas',
            'valor_total',
            'valor_icms',
            'valor_pis',
            'valor_cofins'
        ));
    }

    /**
     * Generate NFC-e report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function nfce(Request $request)
    {
        $data_inicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $data_fim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $query = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('modelo', '65')
            ->whereBetween('created_at', [$data_inicio, $data_fim]);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $notas = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas
        $total_notas = $notas->count();
        $valor_total = $notas->sum('valor_total');
        $valor_icms = $notas->sum('valor_icms');

        return view('fiscalbr::reports.nfce', compact(
            'notas',
            'data_inicio',
            'data_fim',
            'status',
            'total_notas',
            'valor_total',
            'valor_icms'
        ));
    }

    /**
     * Generate SPED report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function sped(Request $request)
    {
        $ano = $request->input('ano', Carbon::now()->year);

        $speds = Sped::where('workspace_id', getActiveWorkSpace())
            ->whereYear('periodo_inicial', $ano)
            ->orderBy('periodo_inicial', 'desc')
            ->get();

        return view('fiscalbr::reports.sped', compact('speds', 'ano'));
    }

    /**
     * Generate NFS-e report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function nfse(Request $request)
    {
        $data_inicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $data_fim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $query = NFSe::where('workspace_id', getActiveWorkSpace())
            ->whereBetween('created_at', [$data_inicio, $data_fim]);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $notas = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas
        $total_notas = $notas->count();
        $valor_total = $notas->sum('valor_servicos');
        $valor_iss = $notas->sum('valor_iss');

        return view('fiscalbr::reports.nfse', compact(
            'notas',
            'data_inicio',
            'data_fim',
            'status',
            'total_notas',
            'valor_total',
            'valor_iss'
        ));
    }

    /**
     * Export report to Excel/CSV.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $tipo = $request->input('tipo'); // nfe, nfce, sped, nfse
        $formato = $request->input('formato', 'csv'); // csv, excel

        // TODO: Implementar exportação para Excel/CSV
        // Pode usar bibliotecas como PhpSpreadsheet ou Laravel Excel

        return response()->json([
            'success' => true,
            'message' => 'Exportação em desenvolvimento'
        ]);
    }
}

