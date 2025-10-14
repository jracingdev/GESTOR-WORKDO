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
                                <h3 class="mb-0">{{ $nfe_emitidas }}</h3>
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
                                <h3 class="mb-0">{{ $nfce_emitidas }}</h3>
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
                                <h3 class="mb-0">R$ {{ number_format($valor_total, 2, ',', '.') }}</h3>
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
                                <h3 class="mb-0">{{ $pendentes }}</h3>
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
                                    @forelse($ultimas_notas as $nota)
                                    <tr>
                                        <td>{{ $nota->numero }}</td>
                                        <td>
                                            @if($nota->modelo == '55')
                                                <span class="badge bg-primary">NF-e</span>
                                            @elseif($nota->modelo == '65')
                                                <span class="badge bg-info">NFC-e</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $nota->modelo }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $nota->destinatario_nome ?? 'CONSUMIDOR' }}</td>
                                        <td>R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            @if($nota->status == 'autorizada')
                                                <span class="badge bg-success">Autorizada</span>
                                            @elseif($nota->status == 'cancelada')
                                                <span class="badge bg-danger">Cancelada</span>
                                            @elseif($nota->status == 'rejeitada')
                                                <span class="badge bg-warning">Rejeitada</span>
                                            @elseif($nota->status == 'processando')
                                                <span class="badge bg-info">Processando</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($nota->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $nota->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($nota->modelo == '55')
                                                <a href="{{ route('fiscalbr.nfe.show', $nota->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @elseif($nota->modelo == '65')
                                                <a href="{{ route('fiscalbr.nfce.show', $nota->id) }}" class="btn btn-sm btn-info">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('Nenhuma nota fiscal encontrada') }}</td>
                                    </tr>
                                    @endforelse
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

