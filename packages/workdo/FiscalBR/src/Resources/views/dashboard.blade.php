@extends('layouts.main')

@section('page-title')
    {{ __('Fiscal Brasileiro') }}
@endsection

@section('page-breadcrumb')
    {{ __('Dashboard Fiscal') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <!-- Card: NF-e Emitidas -->
            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-primary">
                                <i class="ti ti-file-invoice"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('NF-e Emitidas') }}</h6>
                                <h3 class="mb-0">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: NFC-e Emitidas -->
            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-info">
                                <i class="ti ti-receipt"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('NFC-e Emitidas') }}</h6>
                                <h3 class="mb-0">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Valor Total -->
            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-success">
                                <i class="ti ti-currency-real"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Valor Total') }}</h6>
                                <h3 class="mb-0">R$ 0,00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Pendentes -->
            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="theme-avtar bg-warning">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1">{{ __('Pendentes') }}</h6>
                                <h3 class="mb-0">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Ações Rápidas') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('fiscalbr.nfe.create') }}" class="btn btn-primary w-100">
                                    <i class="ti ti-plus"></i> {{ __('Emitir NF-e') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('fiscalbr.nfe.index') }}" class="btn btn-info w-100">
                                    <i class="ti ti-list"></i> {{ __('Listar NF-e') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('fiscalbr.nfce.index') }}" class="btn btn-success w-100">
                                    <i class="ti ti-receipt"></i> {{ __('NFC-e') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('fiscalbr.config.index') }}" class="btn btn-secondary w-100">
                                    <i class="ti ti-settings"></i> {{ __('Configurações') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas NF-e -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Últimas Notas Fiscais') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Número') }}</th>
                                        <th>{{ __('Tipo') }}</th>
                                        <th>{{ __('Destinatário') }}</th>
                                        <th>{{ __('Valor') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Data') }}</th>
                                        <th>{{ __('Ações') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('Nenhuma nota fiscal encontrada') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

