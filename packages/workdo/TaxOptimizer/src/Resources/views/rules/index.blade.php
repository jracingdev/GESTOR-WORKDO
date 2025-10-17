@extends('layouts.app')

@section('page-title')
    {{__('Regras Fiscais (Simulação IA)')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Gerenciamento de Regras Fiscais')}}</h5>
                </div>
                <div class="card-body">
                    <p>Esta seção simula a base de conhecimento da Inteligência Artificial. Na implementação final, estas regras seriam dinâmicas e baseadas em Machine Learning.</p>
                    {{ Form::open(['route' => 'taxoptimizer.rules.store', 'method' => 'POST']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('rule_name', __('Nome da Regra'), ['class' => 'form-label']) }}
                                {{ Form::text('rule_name', null, ['class' => 'form-control', 'placeholder' => __('Ex: Crédito de ICMS sobre Ativo Imobilizado')]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('rule_condition', __('Condição (Simulada)'), ['class' => 'form-label']) }}
                                {{ Form::textarea('rule_condition', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Se CNAE = X e Regime = Y, então aplicar regra Z.')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{__('Salvar Regra')}}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
