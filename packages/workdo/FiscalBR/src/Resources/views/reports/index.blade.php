@extends('layouts.main')

@section('page-title')
    {{ __('Relatórios Fiscais') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('Relatórios') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <!-- Card: Relatório de NF-e -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="theme-avtar bg-primary mb-3">
                                <i class="ti ti-file-invoice" style="font-size: 2rem;"></i>
                            </div>
                            <h5>{{ __('Relatório de NF-e') }}</h5>
                            <p class="text-muted">{{ __('Relatório completo de Notas Fiscais Eletrônicas') }}</p>
                            <a href="{{ route('fiscalbr.reports.nfe') }}" class="btn btn-primary btn-sm">
                                {{ __('Acessar') }} <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Relatório de NFC-e -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="theme-avtar bg-info mb-3">
                                <i class="ti ti-receipt" style="font-size: 2rem;"></i>
                            </div>
                            <h5>{{ __('Relatório de NFC-e') }}</h5>
                            <p class="text-muted">{{ __('Relatório de Notas Fiscais de Consumidor') }}</p>
                            <a href="{{ route('fiscalbr.reports.nfce') }}" class="btn btn-info btn-sm">
                                {{ __('Acessar') }} <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Relatório de SPED -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="theme-avtar bg-success mb-3">
                                <i class="ti ti-file-text" style="font-size: 2rem;"></i>
                            </div>
                            <h5>{{ __('Relatório de SPED') }}</h5>
                            <p class="text-muted">{{ __('Relatório de arquivos SPED Fiscal') }}</p>
                            <a href="{{ route('fiscalbr.reports.sped') }}" class="btn btn-success btn-sm">
                                {{ __('Acessar') }} <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Relatório de NFS-e -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="theme-avtar bg-warning mb-3">
                                <i class="ti ti-file-description" style="font-size: 2rem;"></i>
                            </div>
                            <h5>{{ __('Relatório de NFS-e') }}</h5>
                            <p class="text-muted">{{ __('Relatório de Notas Fiscais de Serviço') }}</p>
                            <a href="{{ route('fiscalbr.reports.nfse') }}" class="btn btn-warning btn-sm">
                                {{ __('Acessar') }} <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Sobre os Relatórios Fiscais') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="ti ti-info-circle"></i> {{ __('Funcionalidades') }}</h6>
                                <ul>
                                    <li>{{ __('Filtros por período e status') }}</li>
                                    <li>{{ __('Estatísticas consolidadas') }}</li>
                                    <li>{{ __('Exportação para Excel/CSV') }}</li>
                                    <li>{{ __('Visualização detalhada') }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="ti ti-calendar"></i> {{ __('Períodos Disponíveis') }}</h6>
                                <ul>
                                    <li>{{ __('Mês atual') }}</li>
                                    <li>{{ __('Personalizado (data início e fim)') }}</li>
                                    <li>{{ __('Ano completo (SPED)') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

