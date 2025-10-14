@extends('layouts.main')

@section('page-title')
    {{ __('Nova NFS-e') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.nfse.index') }}">{{ __('NFS-e') }}</a>,
    {{ __('Nova') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('fiscalbr.nfse.store') }}" method="POST">
            @csrf

            <!-- Tomador do Serviço -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Tomador do Serviço') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tomador_nome">{{ __('Nome/Razão Social') }} <span class="text-danger">*</span></label>
                                <input type="text" name="tomador_nome" id="tomador_nome" class="form-control" value="{{ old('tomador_nome') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tomador_cpf_cnpj">{{ __('CPF/CNPJ') }} <span class="text-danger">*</span></label>
                                <input type="text" name="tomador_cpf_cnpj" id="tomador_cpf_cnpj" class="form-control" value="{{ old('tomador_cpf_cnpj') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tomador_email">{{ __('Email') }}</label>
                                <input type="email" name="tomador_email" id="tomador_email" class="form-control" value="{{ old('tomador_email') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tomador_telefone">{{ __('Telefone') }}</label>
                                <input type="text" name="tomador_telefone" id="tomador_telefone" class="form-control" value="{{ old('tomador_telefone') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados do Serviço -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>{{ __('Dados do Serviço') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="descricao_servico">{{ __('Descrição do Serviço') }} <span class="text-danger">*</span></label>
                        <textarea name="descricao_servico" id="descricao_servico" class="form-control" rows="4" required>{{ old('descricao_servico') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="item_lista_servico">{{ __('Item Lista Serviço (LC 116/2003)') }} <span class="text-danger">*</span></label>
                                <input type="text" name="item_lista_servico" id="item_lista_servico" class="form-control" value="{{ old('item_lista_servico', $fiscalConfig->nfse_item_lista_servico) }}" placeholder="01.01" required>
                                <small class="form-text text-muted">Exemplo: 01.01, 07.02, etc.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_cnae">{{ __('Código CNAE') }}</label>
                                <input type="text" name="codigo_cnae" id="codigo_cnae" class="form-control" value="{{ old('codigo_cnae', $fiscalConfig->nfse_codigo_cnae) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="aliquota_iss">{{ __('Alíquota ISS (%)') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="aliquota_iss" id="aliquota_iss" class="form-control" value="{{ old('aliquota_iss', $fiscalConfig->nfse_aliquota_iss) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>{{ __('Valores') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_servicos">{{ __('Valor dos Serviços') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor_servicos" id="valor_servicos" class="form-control" value="{{ old('valor_servicos') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_deducoes">{{ __('Deduções') }}</label>
                                <input type="number" step="0.01" name="valor_deducoes" id="valor_deducoes" class="form-control" value="{{ old('valor_deducoes', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('ISS Retido') }}</label>
                                <select name="iss_retido" class="form-control">
                                    <option value="nao" {{ old('iss_retido') == 'nao' ? 'selected' : '' }}>Não</option>
                                    <option value="sim" {{ old('iss_retido') == 'sim' ? 'selected' : '' }}>Sim</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group text-end mt-3">
                <a href="{{ route('fiscalbr.nfse.index') }}" class="btn btn-secondary">
                    {{ __('Cancelar') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> {{ __('Salvar NFS-e') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

