@extends('layouts.main')

@section('page-title')
    {{ __('NFS-e - Nota Fiscal de Serviço Eletrônica') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('NFS-e') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('fiscalbr.nfse.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Nova NFS-e') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{ __('RPS') }}</th>
                                <th>{{ __('NFS-e') }}</th>
                                <th>{{ __('Data Emissão') }}</th>
                                <th>{{ __('Tomador') }}</th>
                                <th>{{ __('Valor') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nfses as $nfse)
                                <tr>
                                    <td>{{ $nfse->numero_rps }}/{{ $nfse->serie_rps }}</td>
                                    <td>{{ $nfse->numero_nfse ?? '-' }}</td>
                                    <td>{{ $nfse->data_emissao->format('d/m/Y') }}</td>
                                    <td>{{ $nfse->tomador_nome }}</td>
                                    <td>R$ {{ number_format($nfse->valor_servicos, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $nfse->status_badge }}">
                                            {{ $nfse->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('fiscalbr.nfse.show', $nfse->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizar') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('Nenhuma NFS-e encontrada.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $nfses->links() }}
    </div>
</div>
@endsection

