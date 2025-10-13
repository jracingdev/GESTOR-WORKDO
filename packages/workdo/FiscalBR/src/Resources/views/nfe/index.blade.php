@extends('layouts.main')

@section('page-title')
    {{ __('NF-e - Nota Fiscal Eletrônica') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('NF-e') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('fiscalbr.nfe.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Criar NF-e') }}">
        <i class="ti ti-plus"></i>
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
                                <th>{{ __('Número') }}</th>
                                <th>{{ __('Série') }}</th>
                                <th>{{ __('Chave de Acesso') }}</th>
                                <th>{{ __('Destinatário') }}</th>
                                <th>{{ __('Valor Total') }}</th>
                                <th>{{ __('Data Emissão') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="200px">{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">{{ __('Nenhuma NF-e encontrada') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

