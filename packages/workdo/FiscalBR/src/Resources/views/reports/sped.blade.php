@extends('layouts.main')

@section('page-title')
    {{ __('Relatório de SPED Fiscal') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('Relatórios') }},
    {{ __('SPED') }}
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
                <form method="GET" action="{{ route('fiscalbr.reports.sped') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Ano') }}</label>
                                <select name="ano" class="form-control">
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ $ano == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
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

        <!-- Tabela de SPED -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Arquivos SPED Fiscal - Ano') }} {{ $ano }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Período') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Tamanho') }}</th>
                                <th>{{ __('Data Geração') }}</th>
                                <th>{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($speds as $sped)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sped->periodo_inicial)->format('m/Y') }}</td>
                                <td>{{ $sped->tipo }}</td>
                                <td>
                                    @if($sped->status == 'gerado')
                                        <span class="badge bg-success">Gerado</span>
                                    @elseif($sped->status == 'processando')
                                        <span class="badge bg-info">Processando</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($sped->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($sped->tamanho_arquivo / 1024, 2) }} KB</td>
                                <td>{{ $sped->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('fiscalbr.sped.show', $sped->id) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('fiscalbr.sped.download', $sped->id) }}" class="btn btn-sm btn-success">
                                        <i class="ti ti-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('Nenhum arquivo SPED encontrado para este ano') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informações -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Informações sobre SPED Fiscal') }}</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ __('Prazo de Entrega:') }}</strong> {{ __('Até o dia 20 do mês seguinte') }}</p>
                <p><strong>{{ __('Formato:') }}</strong> {{ __('Arquivo texto (.txt) pipe-delimited') }}</p>
                <p><strong>{{ __('Validação:') }}</strong> {{ __('PVA (Programa Validador e Assinador)') }}</p>
                <p><strong>{{ __('Periodicidade:') }}</strong> {{ __('Mensal') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

