@extends('layouts.main')

@section('page-title')
    {{ __('Configurações Fiscais') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('Configurações') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Dados da Empresa') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('fiscalbr.config.empresa.update') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cnpj">{{ __('CNPJ') }} <span class="text-danger">*</span></label>
                                <input type="text" name="cnpj" id="cnpj" class="form-control" placeholder="00.000.000/0000-00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="razao_social">{{ __('Razão Social') }} <span class="text-danger">*</span></label>
                                <input type="text" name="razao_social" id="razao_social" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome_fantasia">{{ __('Nome Fantasia') }}</label>
                                <input type="text" name="nome_fantasia" id="nome_fantasia" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inscricao_estadual">{{ __('Inscrição Estadual') }}</label>
                                <input type="text" name="inscricao_estadual" id="inscricao_estadual" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inscricao_municipal">{{ __('Inscrição Municipal') }}</label>
                                <input type="text" name="inscricao_municipal" id="inscricao_municipal" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cnae">{{ __('CNAE') }}</label>
                                <input type="text" name="cnae" id="cnae" class="form-control" placeholder="0000-0/00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="regime_tributario">{{ __('Regime Tributário') }} <span class="text-danger">*</span></label>
                                <select name="regime_tributario" id="regime_tributario" class="form-control" required>
                                    <option value="simples_nacional">{{ __('Simples Nacional') }}</option>
                                    <option value="lucro_presumido">{{ __('Lucro Presumido') }}</option>
                                    <option value="lucro_real">{{ __('Lucro Real') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ambiente">{{ __('Ambiente') }} <span class="text-danger">*</span></label>
                                <select name="ambiente" id="ambiente" class="form-control" required>
                                    <option value="homologacao">{{ __('Homologação (Testes)') }}</option>
                                    <option value="producao">{{ __('Produção') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Salvar Configurações') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Certificado Digital -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Certificado Digital') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('fiscalbr.config.certificado.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="certificado">{{ __('Arquivo do Certificado (.pfx)') }} <span class="text-danger">*</span></label>
                                <input type="file" name="certificado" id="certificado" class="form-control" accept=".pfx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha_certificado">{{ __('Senha do Certificado') }} <span class="text-danger">*</span></label>
                                <input type="password" name="senha_certificado" id="senha_certificado" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-info" onclick="testCertificado()">{{ __('Testar Certificado') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Salvar Certificado') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Numeração -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Numeração de Documentos') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Série NF-e') }}</label>
                            <input type="text" class="form-control" value="1" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Próximo Número NF-e') }}</label>
                            <input type="text" class="form-control" value="1" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Série NFC-e') }}</label>
                            <input type="text" class="form-control" value="1" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Próximo Número NFC-e') }}</label>
                            <input type="text" class="form-control" value="1" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testCertificado() {
    // TODO: Implementar teste de certificado via AJAX
    alert('Funcionalidade em desenvolvimento');
}
</script>
@endsection

