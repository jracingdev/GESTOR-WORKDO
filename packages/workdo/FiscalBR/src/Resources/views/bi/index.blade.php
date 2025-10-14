@extends('layouts.main')

@section('page-title')
    {{ __('Business Intelligence Fiscal') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('BI') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <!-- KPIs Principais -->
        <div class="row">
            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-success">
                                <i class="ti ti-currency-real"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Faturamento Total') }}</h6>
                                <h3 class="mb-0">R$ {{ number_format($kpis['faturamento_total'], 2, ',', '.') }}</h3>
                                <small class="{{ $kpis['crescimento'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="ti ti-{{ $kpis['crescimento'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ number_format(abs($kpis['crescimento']), 2) }}% vs período anterior
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-warning">
                                <i class="ti ti-receipt-tax"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Total de Impostos') }}</h6>
                                <h3 class="mb-0">R$ {{ number_format($kpis['total_impostos'], 2, ',', '.') }}</h3>
                                <small class="text-muted">
                                    {{ __('Carga Tributária:') }} {{ number_format($kpis['carga_tributaria'], 2) }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-primary">
                                <i class="ti ti-file-invoice"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Total de Notas') }}</h6>
                                <h3 class="mb-0">{{ number_format($kpis['total_notas'], 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    {{ __('Ticket Médio:') }} R$ {{ number_format($kpis['ticket_medio'], 2, ',', '.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-{{ $kpis['taxa_rejeicao'] < 5 ? 'success' : 'danger' }}">
                                <i class="ti ti-alert-circle"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Taxa de Rejeição') }}</h6>
                                <h3 class="mb-0">{{ number_format($kpis['taxa_rejeicao'], 2) }}%</h3>
                                <small class="text-muted">
                                    {{ __('Meta: < 5%') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="row">
            <!-- Faturamento Mensal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Faturamento Mensal (Últimos 12 Meses)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartFaturamentoMensal" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribuição por Status -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Notas por Status') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartNotasStatus" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impostos Mensais -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Evolução de Impostos (ICMS, PIS, COFINS)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartImpostosMensais" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhamento de Impostos -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-tax" style="font-size: 3rem; color: #007bff;"></i>
                        <h6 class="mt-3">{{ __('ICMS Total') }}</h6>
                        <h3>R$ {{ number_format($kpis['total_icms'], 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-tax" style="font-size: 3rem; color: #28a745;"></i>
                        <h6 class="mt-3">{{ __('PIS Total') }}</h6>
                        <h3>R$ {{ number_format($kpis['total_pis'], 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-tax" style="font-size: 3rem; color: #ffc107;"></i>
                        <h6 class="mt-3">{{ __('COFINS Total') }}</h6>
                        <h3>R$ {{ number_format($kpis['total_cofins'], 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Dados do backend
const faturamentoData = @json($faturamento_mensal);
const impostosData = @json($impostos_mensais);
const statusData = @json($notas_por_status);

// Gráfico de Faturamento Mensal
const ctxFaturamento = document.getElementById('chartFaturamentoMensal').getContext('2d');
new Chart(ctxFaturamento, {
    type: 'line',
    data: {
        labels: faturamentoData.labels,
        datasets: [{
            label: 'Faturamento (R$)',
            data: faturamentoData.valores,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Quantidade de Notas',
            data: faturamentoData.quantidades,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR');
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            if (context.datasetIndex === 0) {
                                label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            } else {
                                label += context.parsed.y;
                            }
                        }
                        return label;
                    }
                }
            }
        }
    }
});

// Gráfico de Notas por Status
const ctxStatus = document.getElementById('chartNotasStatus').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: statusData.labels,
        datasets: [{
            data: statusData.valores,
            backgroundColor: statusData.cores
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Gráfico de Impostos Mensais
const ctxImpostos = document.getElementById('chartImpostosMensais').getContext('2d');
new Chart(ctxImpostos, {
    type: 'bar',
    data: {
        labels: impostosData.labels,
        datasets: [{
            label: 'ICMS',
            data: impostosData.icms,
            backgroundColor: 'rgba(0, 123, 255, 0.7)',
        }, {
            label: 'PIS',
            data: impostosData.pis,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
        }, {
            label: 'COFINS',
            data: impostosData.cofins,
            backgroundColor: 'rgba(255, 193, 7, 0.7)',
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                stacked: true,
            },
            y: {
                stacked: true,
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>
@endsection

