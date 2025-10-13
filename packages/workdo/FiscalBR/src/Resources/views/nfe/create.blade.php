@extends('layouts.main')

@section('page-title')
    {{ __('Emitir NF-e') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.nfe.index') }}">{{ __('NF-e') }}</a>,
    {{ __('Emitir') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('fiscalbr.nfe.store') }}">
            @csrf
            
            <!-- Dados do Destinatário -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Dados do Destinatário') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destinatario_cpf_cnpj">{{ __('CPF/CNPJ') }} <span class="text-danger">*</span></label>
                                <input type="text" name="destinatario_cpf_cnpj" id="destinatario_cpf_cnpj" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destinatario_nome">{{ __('Nome/Razão Social') }} <span class="text-danger">*</span></label>
                                <input type="text" name="destinatario_nome" id="destinatario_nome" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destinatario_ie">{{ __('Inscrição Estadual') }}</label>
                                <input type="text" name="destinatario_ie" id="destinatario_ie" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destinatario_uf">{{ __('UF') }} <span class="text-danger">*</span></label>
                                <select name="destinatario_uf" id="destinatario_uf" class="form-control" required>
                                    <option value="">{{ __('Selecione') }}</option>
                                    <option value="AC">AC</option>
                                    <option value="AL">AL</option>
                                    <option value="AP">AP</option>
                                    <option value="AM">AM</option>
                                    <option value="BA">BA</option>
                                    <option value="CE">CE</option>
                                    <option value="DF">DF</option>
                                    <option value="ES">ES</option>
                                    <option value="GO">GO</option>
                                    <option value="MA">MA</option>
                                    <option value="MT">MT</option>
                                    <option value="MS">MS</option>
                                    <option value="MG">MG</option>
                                    <option value="PA">PA</option>
                                    <option value="PB">PB</option>
                                    <option value="PR">PR</option>
                                    <option value="PE">PE</option>
                                    <option value="PI">PI</option>
                                    <option value="RJ">RJ</option>
                                    <option value="RN">RN</option>
                                    <option value="RS">RS</option>
                                    <option value="RO">RO</option>
                                    <option value="RR">RR</option>
                                    <option value="SC">SC</option>
                                    <option value="SP">SP</option>
                                    <option value="SE">SE</option>
                                    <option value="TO">TO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos/Serviços -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>{{ __('Produtos/Serviços') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        {{ __('Funcionalidade de adicionar produtos em desenvolvimento. Por enquanto, a NF-e será criada como rascunho.') }}
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="text-end mt-3">
                <a href="{{ route('fiscalbr.nfe.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Criar Rascunho') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

