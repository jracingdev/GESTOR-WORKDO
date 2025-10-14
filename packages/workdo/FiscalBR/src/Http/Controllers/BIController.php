<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\Sped;
use Workdo\FiscalBR\Entities\NFSe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BIController extends Controller
{
    /**
     * Display the BI dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Período padrão: últimos 12 meses
        $periodo_inicio = Carbon::now()->subMonths(12)->startOfMonth();
        $periodo_fim = Carbon::now()->endOfMonth();

        // KPIs Principais
        $kpis = $this->getKPIs($periodo_inicio, $periodo_fim);

        // Dados para gráficos
        $faturamento_mensal = $this->getFaturamentoMensal($periodo_inicio, $periodo_fim);
        $impostos_mensais = $this->getImpostosMensais($periodo_inicio, $periodo_fim);
        $notas_por_status = $this->getNotasPorStatus();
        $top_produtos = $this->getTopProdutos();
        $distribuicao_cfop = $this->getDistribuicaoCFOP();

        return view('fiscalbr::bi.index', compact(
            'kpis',
            'faturamento_mensal',
            'impostos_mensais',
            'notas_por_status',
            'top_produtos',
            'distribuicao_cfop'
        ));
    }

    /**
     * Get main KPIs.
     *
     * @param Carbon $periodo_inicio
     * @param Carbon $periodo_fim
     * @return array
     */
    private function getKPIs($periodo_inicio, $periodo_fim)
    {
        $workspace_id = getActiveWorkSpace();

        // Faturamento total
        $faturamento_total = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->sum('valor_total');

        // Total de impostos
        $total_icms = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->sum('valor_icms');

        $total_pis = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->sum('valor_pis');

        $total_cofins = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->sum('valor_cofins');

        $total_impostos = $total_icms + $total_pis + $total_cofins;

        // Carga tributária (%)
        $carga_tributaria = $faturamento_total > 0 
            ? ($total_impostos / $faturamento_total) * 100 
            : 0;

        // Total de notas emitidas
        $total_notas = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->count();

        // Ticket médio
        $ticket_medio = $total_notas > 0 
            ? $faturamento_total / $total_notas 
            : 0;

        // Taxa de rejeição
        $total_rejeitadas = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'rejeitada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->count();

        $total_tentativas = NFe::where('workspace_id', $workspace_id)
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->count();

        $taxa_rejeicao = $total_tentativas > 0 
            ? ($total_rejeitadas / $total_tentativas) * 100 
            : 0;

        // Comparação com período anterior
        $periodo_anterior_inicio = Carbon::parse($periodo_inicio)->subMonths(12);
        $periodo_anterior_fim = Carbon::parse($periodo_inicio)->subDay();

        $faturamento_anterior = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_anterior_inicio, $periodo_anterior_fim])
            ->sum('valor_total');

        $crescimento = $faturamento_anterior > 0 
            ? (($faturamento_total - $faturamento_anterior) / $faturamento_anterior) * 100 
            : 0;

        return [
            'faturamento_total' => $faturamento_total,
            'total_impostos' => $total_impostos,
            'total_icms' => $total_icms,
            'total_pis' => $total_pis,
            'total_cofins' => $total_cofins,
            'carga_tributaria' => $carga_tributaria,
            'total_notas' => $total_notas,
            'ticket_medio' => $ticket_medio,
            'taxa_rejeicao' => $taxa_rejeicao,
            'crescimento' => $crescimento,
        ];
    }

    /**
     * Get monthly revenue data.
     *
     * @param Carbon $periodo_inicio
     * @param Carbon $periodo_fim
     * @return array
     */
    private function getFaturamentoMensal($periodo_inicio, $periodo_fim)
    {
        $workspace_id = getActiveWorkSpace();

        $dados = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(valor_total) as total'),
                DB::raw('COUNT(*) as quantidade')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = [];
        $valores = [];
        $quantidades = [];

        foreach ($dados as $item) {
            $labels[] = Carbon::createFromFormat('Y-m', $item->mes)->format('M/Y');
            $valores[] = (float) $item->total;
            $quantidades[] = (int) $item->quantidade;
        }

        return [
            'labels' => $labels,
            'valores' => $valores,
            'quantidades' => $quantidades,
        ];
    }

    /**
     * Get monthly taxes data.
     *
     * @param Carbon $periodo_inicio
     * @param Carbon $periodo_fim
     * @return array
     */
    private function getImpostosMensais($periodo_inicio, $periodo_fim)
    {
        $workspace_id = getActiveWorkSpace();

        $dados = NFe::where('workspace_id', $workspace_id)
            ->where('status', 'autorizada')
            ->whereBetween('created_at', [$periodo_inicio, $periodo_fim])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(valor_icms) as icms'),
                DB::raw('SUM(valor_pis) as pis'),
                DB::raw('SUM(valor_cofins) as cofins')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = [];
        $icms = [];
        $pis = [];
        $cofins = [];

        foreach ($dados as $item) {
            $labels[] = Carbon::createFromFormat('Y-m', $item->mes)->format('M/Y');
            $icms[] = (float) $item->icms;
            $pis[] = (float) $item->pis;
            $cofins[] = (float) $item->cofins;
        }

        return [
            'labels' => $labels,
            'icms' => $icms,
            'pis' => $pis,
            'cofins' => $cofins,
        ];
    }

    /**
     * Get notes distribution by status.
     *
     * @return array
     */
    private function getNotasPorStatus()
    {
        $workspace_id = getActiveWorkSpace();

        $dados = NFe::where('workspace_id', $workspace_id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $labels = [];
        $valores = [];
        $cores = [
            'autorizada' => '#28a745',
            'cancelada' => '#dc3545',
            'rejeitada' => '#ffc107',
            'rascunho' => '#6c757d',
            'processando' => '#17a2b8',
        ];

        foreach ($dados as $item) {
            $labels[] = ucfirst($item->status);
            $valores[] = (int) $item->total;
        }

        return [
            'labels' => $labels,
            'valores' => $valores,
            'cores' => array_values($cores),
        ];
    }

    /**
     * Get top products.
     *
     * @return array
     */
    private function getTopProdutos()
    {
        // TODO: Implementar quando houver relacionamento com produtos
        return [
            'labels' => [],
            'valores' => [],
        ];
    }

    /**
     * Get CFOP distribution.
     *
     * @return array
     */
    private function getDistribuicaoCFOP()
    {
        // TODO: Implementar quando houver campo CFOP nas notas
        return [
            'labels' => [],
            'valores' => [],
        ];
    }

    /**
     * Export BI data to Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // TODO: Implementar exportação
        return response()->json([
            'success' => true,
            'message' => 'Exportação em desenvolvimento'
        ]);
    }
}

