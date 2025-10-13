@extends('layouts.main')

@section('page-title')
    {{ __('NFC-e - Nota Fiscal de Consumidor Eletrônica') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('NFC-e') }}
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
                                <th>{{ __('Valor Total') }}</th>
                                <th>{{ __('Data Emissão') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="150px">{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">{{ __('Nenhuma NFC-e encontrada') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

