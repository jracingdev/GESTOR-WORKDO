@extends('layouts.main')

@section('page-title')
    {{ __('Gerar SPED Fiscal') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.sped.index') }}">{{ __('SPED Fiscal') }}</a>,
    {{ __('Gerar') }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Gerar Arquivo SPED Fiscal (EFD ICMS/IPI)') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('fiscalbr.sped.generate') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ano">{{ __('Ano') }} <span class="text-danger">*</span></label>
                                <select name="ano" id="ano" class="form-control" required>
                                    <option value="">{{ __('Selecione') }}</option>
                                    @foreach($anos as $ano)
                                        <option value="{{ $ano }}" {{ old('ano') == $ano ? 'selected' : '' }}>{{ $ano }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mes">{{ __('Mês') }} <span class="text-danger">*</span></label>
                                <select name="mes" id="mes" class="form-control" required>
                                    <option value="">{{ __('Selecione') }}</option>
                                    @foreach($meses as $num => $nome)
                                        <option value="{{ $num }}" {{ old('mes') == $num ? 'selected' : '' }}>{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="perfil">{{ __('Perfil de Apresentação') }} <span class="text-danger">*</span></label>
                        <select name="perfil" id="perfil" class="form-control" required>
                            <option value="">{{ __('Selecione') }}</option>
                            <option value="A" {{ old('perfil') == 'A' ? 'selected' : '' }}>Perfil A - Completo</option>
                            <option value="B" {{ old('perfil') == 'B' ? 'selected' : '' }}>Perfil B - Simplificado</option>
                            <option value="C" {{ old('perfil') == 'C' ? 'selected' : '' }}>Perfil C - Lucro Presumido</option>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Perfil A:</strong> Escrituração completa<br>
                            <strong>Perfil B:</strong> Escrituração simplificada (empresas com faturamento até R$ 3,6 milhões/ano)<br>
                            <strong>Perfil C:</strong> Empresas tributadas pelo Lucro Presumido
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i>
                        <strong>Atenção:</strong> O arquivo SPED será gerado com base nas NF-e e NFC-e emitidas no período selecionado.
                        Certifique-se de que todas as notas fiscais do período foram emitidas antes de gerar o SPED.
                    </div>

                    <div class="form-group text-end">
                        <a href="{{ route('fiscalbr.sped.index') }}" class="btn btn-secondary">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-file-export"></i> {{ __('Gerar SPED') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

