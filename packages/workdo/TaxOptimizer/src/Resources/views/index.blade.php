@extends('layouts.app')

@section('page-title')
    {{__('Tax Optimizer')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Ingestão de Dados Fiscais')}}</h5>
                </div>
                <div class="card-body">
                    <p>Utilize esta seção para enviar os dados necessários para a análise de otimização tributária e recuperação de créditos.</p>
                    {{ Form::open(['route' => 'taxoptimizer.upload', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('cnae', __('CNAE Principal'), ['class' => 'form-label']) }}
                                {{ Form::text('cnae', null, ['class' => 'form-control', 'placeholder' => __('Ex: 4711-3/02')]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('xml_files', __('Arquivos XML (NF-e, CT-e)'), ['class' => 'form-label']) }}
                                {{ Form::file('xml_files[]', ['class' => 'form-control', 'multiple' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4 align-self-end">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">{{__('Iniciar Análise')}}</button>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Histórico de Análises')}}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{__('ID')}}</th>
                                <th>{{__('CNAE')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Resumo')}}</th>
                                <th>{{__('Data')}}</th>
                                <th>{{__('Ações')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\Workdo\TaxOptimizer\Models\TaxAnalysis::all() as $analysis)
                                <tr>
                                    <td>{{->id}}</td>
                                    <td>{{->cnae}}</td>
                                    <td><span class="badge bg-{{ $analysis->status == 'completed' ? 'success' : 'warning' }}">{{->status}}</span></td>
                                    <td>{{->report_summary}}</td>
                                    <td>{{->created_at->format('d/m/Y H:i')}}</td>
                                    <td>
                                        <a href="{{route('taxoptimizer.analyze', $analysis->id)}}" class="btn btn-sm btn-info">{{__('Ver Relatório')}}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">{{__('Nenhuma análise encontrada.')}}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
