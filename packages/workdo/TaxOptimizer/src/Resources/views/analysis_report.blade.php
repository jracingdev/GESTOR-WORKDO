@extends('layouts.app')

@section('page-title')
    {{__('Relatório de Otimização Tributária')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Análise Fiscal #')}}{{$analysis->id}} - {{__('CNAE:')}} {{$analysis->cnae}}</h5>
                    <div class="card-header-right">
                        <a href="#" class="btn btn-sm btn-primary" onclick="window.print()">{{__('Imprimir Relatório')}}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="mb-3">{{__('Resumo da Análise')}}</h4>
                            <p>{{->report_summary}}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        {{-- Recomendação de Regime --}}
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{__('Regime Tributário Ideal')}}</h5>
                                    <p class="card-text fs-3">{{->analysis_result['best_regime'] ?? 'N/A'}}</p>
                                </div>
                            </div>
                        </div>
                        {{-- Economia Potencial --}}
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">{{__('Economia Potencial (Simulação)')}}</h5>
                                    <p class="card-text fs-3">R$ {{number_format(->analysis_result['economy_potential'] ?? 0, 2, ',', '.')}}</p>
                                </div>
                            </div>
                        </div>
                        {{-- Créditos Identificados --}}
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{__('Créditos a Recuperar')}}</h5>
                                    <p class="card-text fs-3">R$ {{number_format(->analysis_result['recovery_amount'] ?? 0, 2, ',', '.')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h4 class="mb-3">{{__('Detalhes dos Créditos Identificados')}}</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{__('Crédito')}}</th>
                                <th>{{__('Valor Estimado')}}</th>
                                <th>{{__('Base Legal (Simulada)')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(->analysis_result['credits_found'] ?? [] as $credit)
                                <tr>
                                    <td>{{['name']}}</td>
                                    <td>R$ {{number_format(['amount'], 2, ',', '.')}}</td>
                                    <td>{{['legal_base']}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">{{__('Nenhum crédito fiscal identificado nesta análise.')}}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
