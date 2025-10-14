@extends('layouts.main')

@section('page-title')
    {{ __('SPED Fiscal') }} - {{ $sped->periodo }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.sped.index') }}">{{ __('SPED Fiscal') }}</a>,
    {{ __('Visualizar') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Status Card -->
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>SPED Fiscal - {{ $sped->periodo }}</h4>
                        <p class="text-muted mb-0">
                            <strong>Tipo:</strong> {{ $sped->tipo }}<br>
                            <strong>Perfil:</strong> 
                            @if($sped->perfil === 'A')
                                Perfil A (Completo)
                            @elseif($sped->perfil === 'B')
                                Perfil B (Simplificado)
                            @else
                                Perfil C (Lucro Presumido)
                            @endif
                        </p>
                        <p class="text-muted mb-0">
                            <strong>Status:</strong> 
                            @if($sped->status === 'gerando')
                                <span class="badge bg-warning">Gerando</span>
                            @elseif($sped->status === 'gerado')
                                <span class="badge bg-success">Gerado</span>
                            @elseif($sped->status === 'validado')
                                <span class="badge bg-info">Validado</span>
                            @elseif($sped->status === 'transmitido')
                                <span class="badge bg-primary">Transmitido</span>
                            @else
                                <span class="badge bg-danger">Erro</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group">
                            <a href="{{ route('fiscalbr.sped.download', $sped->id) }}" class="btn btn-success">
                                <i class="ti ti-download"></i> {{ __('Download') }}
                            </a>
                            <button type="button" class="btn btn-primary" onclick="enviarContabilidade({{ $sped->id }})">
                                <i class="ti ti-send"></i> {{ __('Enviar para Contabilidade') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Arquivo -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Informações do Arquivo') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('Nome do Arquivo') }}:</strong> {{ $sped->nome_arquivo }}</p>
                        <p><strong>{{ __('Data de Geração') }}:</strong> {{ $sped->data_geracao ? $sped->data_geracao->format('d/m/Y H:i:s') : '-' }}</p>
                        <p><strong>{{ __('Total de Linhas') }}:</strong> {{ number_format($totalLinhas, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6">
                        @if($sped->data_validacao)
                            <p><strong>{{ __('Data de Validação') }}:</strong> {{ $sped->data_validacao->format('d/m/Y H:i:s') }}</p>
                        @endif
                        @if($sped->data_transmissao)
                            <p><strong>{{ __('Data de Transmissão') }}:</strong> {{ $sped->data_transmissao->format('d/m/Y H:i:s') }}</p>
                            <p><strong>{{ __('Recibo') }}:</strong> {{ $sped->recibo_transmissao }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Prévia do Arquivo -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Prévia do Arquivo (Primeiras 50 linhas)') }}</h5>
            </div>
            <div class="card-body">
                <pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;">{{ implode("\n", array_slice(explode("\r\n", $sped->arquivo), 0, 50)) }}</pre>
            </div>
        </div>

        @if($sped->erros_validacao)
            <!-- Erros de Validação -->
            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5>{{ __('Erros de Validação') }}</h5>
                </div>
                <div class="card-body">
                    <pre>{{ $sped->erros_validacao }}</pre>
                </div>
            </div>
        @endif

        <!-- Instruções -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h5>{{ __('Instruções') }}</h5>
            </div>
            <div class="card-body">
                <h6>{{ __('Como validar o arquivo SPED:') }}</h6>
                <ol>
                    <li>Faça o download do arquivo SPED</li>
                    <li>Acesse o <a href="https://www.gov.br/receitafederal/pt-br/assuntos/orientacao-tributaria/declaracoes-e-demonstrativos/sped-sistema-publico-de-escrituracao-digital/escrituracao-fiscal-digital-efd/escrituracao-fiscal-digital-efd" target="_blank">site da Receita Federal</a></li>
                    <li>Baixe o Programa Validador e Assinador (PVA)</li>
                    <li>Abra o arquivo SPED no PVA</li>
                    <li>Clique em "Validar"</li>
                    <li>Corrija eventuais erros e gere novamente</li>
                    <li>Após validado, assine digitalmente com seu certificado A1 ou A3</li>
                    <li>Transmita para a SEFAZ através do PVA</li>
                </ol>

                <h6 class="mt-3">{{ __('Prazos de Entrega:') }}</h6>
                <p>O SPED Fiscal (EFD ICMS/IPI) deve ser entregue até o <strong>dia 20 do mês seguinte</strong> ao período de apuração.</p>
                <p><strong>Exemplo:</strong> SPED de Janeiro/2025 deve ser entregue até 20/02/2025.</p>
            </div>
        </div>
    </div>
</div>

<script>
function enviarContabilidade(spedId) {
    if (!confirm('Deseja enviar este SPED para a contabilidade?')) {
        return;
    }

    fetch(`/fiscalbr/sped/${spedId}/enviar-contabilidade`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erro ao enviar SPED: ' + error);
    });
}
</script>
@endsection

