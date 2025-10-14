@extends('layouts.main')

@section('page-title')
    {{ __('Relatório de NFS-e') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('Relatórios') }},
    {{ __('NFS-e') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Filtros') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('fiscalbr.reports.nfse') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Data Início') }}</label>
                                <input type="date" name="data_inicio" class="form-control" value="{{ $data_inicio }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Data Fim') }}</label>
                                <input type="date" name="data_fim" class="form-control" value="{{ $data_fim }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ __('Todos') }}</option>
                                    <option value="autorizada" {{ $status == 'autorizada' ? 'selected' : '' }}>{{ __('Autorizada') }}</option>
                                    <option value="cancelada" {{ $status == 'cancelada' ? 'selected' : '' }}>{{ __('Cancelada') }}</option>
                                    <option value="rejeitada" {{ $status == 'rejeitada' ? 'selected' : '' }}>{{ __('Rejeitada') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-filter"></i> {{ __('Filtrar') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Total de Notas') }}</h6>
                        <h3>{{ $total_notas }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Valor Total') }}</h6>
                        <h3>R$ {{ number_format($valor_total, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('ISS Total') }}</h6>
                        <h3>R$ {{ number_format($valor_iss, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Notas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ __('Notas Fiscais de Serviço') }}</h5>
                <button class="btn btn-success btn-sm" onclick="exportarExcel()">
                    <i class="ti ti-file-spreadsheet"></i> {{ __('Exportar Excel') }}
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Número') }}</th>
                                <th>{{ __('Tomador') }}</th>
                                <th>{{ __('Valor Serviços') }}</th>
                                <th>{{ __('ISS') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Data') }}</th>
                                <th>{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notas as $nota)
                            <tr>
                                <td>{{ $nota->numero }}</td>
                                <td>{{ $nota->tomador_nome }}</td>
                                <td>R$ {{ number_format($nota->valor_servicos, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($nota->valor_iss, 2, ',', '.') }}</td>
                                <td>
                                    @if($nota->status == 'autorizada')
                                        <span class="badge bg-success">Autorizada</span>
                                    @elseif($nota->status == 'cancelada')
                                        <span class="badge bg-danger">Cancelada</span>
                                    @elseif($nota->status == 'rejeitada')
                                        <span class="badge bg-warning">Rejeitada</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($nota->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $nota->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('fiscalbr.nfse.show', $nota->id) }}" class="btn btn-sm btn-warning">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('Nenhuma nota encontrada') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportarExcel() {
    alert('Exportação para Excel em desenvolvimento');
}
</script>
@endsection

