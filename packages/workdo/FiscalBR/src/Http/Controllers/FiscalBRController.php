<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\Sped;
use Workdo\FiscalBR\Entities\NFSe;
use Illuminate\Support\Facades\DB;

class FiscalBRController extends Controller
{
    /**
     * Display the fiscal dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Métricas de NF-e
        $nfe_emitidas = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('modelo', '55')
            ->where('status', 'autorizada')
            ->count();

        // Métricas de NFC-e
        $nfce_emitidas = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('modelo', '65')
            ->where('status', 'autorizada')
            ->count();

        // Valor total de notas autorizadas
        $valor_total = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('status', 'autorizada')
            ->sum('valor_total');

        // Notas pendentes (rascunho ou processando)
        $pendentes = NFe::where('workspace_id', getActiveWorkSpace())
            ->whereIn('status', ['rascunho', 'processando'])
            ->count();

        // Últimas 10 notas fiscais
        $ultimas_notas = NFe::where('workspace_id', getActiveWorkSpace())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Métricas adicionais
        $nfe_canceladas = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('status', 'cancelada')
            ->count();

        $nfe_rejeitadas = NFe::where('workspace_id', getActiveWorkSpace())
            ->where('status', 'rejeitada')
            ->count();

        // SPED Fiscal - últimos arquivos gerados
        $ultimos_sped = Sped::where('workspace_id', getActiveWorkSpace())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // NFS-e emitidas
        $nfse_emitidas = NFSe::where('workspace_id', getActiveWorkSpace())
            ->where('status', 'autorizada')
            ->count();

        return view('fiscalbr::dashboard', compact(
            'nfe_emitidas',
            'nfce_emitidas',
            'valor_total',
            'pendentes',
            'ultimas_notas',
            'nfe_canceladas',
            'nfe_rejeitadas',
            'ultimos_sped',
            'nfse_emitidas'
        ));
    }
}

