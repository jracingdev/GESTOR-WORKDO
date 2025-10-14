@extends('layouts.main')

@section('page-title')
    {{ __('NFS-e') }} - {{ $nfse->numero_nfse ?? 'RPS ' . $nfse->numero_rps }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.nfse.index') }}">{{ __('NFS-e') }}</a>,
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
                        <h4>NFS-e {{ $nfse->numero_nfse ?? 'RPS ' . $nfse->numero_rps . '/' . $nfse->serie_rps }}</h4>
                        <p class="text-muted mb-0">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $nfse->status_badge }}">{{ $nfse->status_label }}</span>
                        </p>
                        @if($nfse->codigo_verificacao)
                            <p class="text-muted mb-0">
                                <strong>Código de Verificação:</strong> {{ $nfse->codigo_verificacao }}
                            </p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        @if($nfse->status === 'rascunho' || $nfse->status === 'rps_gerado')
                            <button type="button" class="btn btn-success" onclick="transmitir()">
                                <i class="ti ti-send"></i> {{ __('Transmitir') }}
                            </button>
                        @endif
                        
                        @if($nfse->xml)
                            <a href="{{ route('fiscalbr.nfse.xml', $nfse->id) }}" class="btn btn-info">
                                <i class="ti ti-file-code"></i> {{ __('XML') }}
                            </a>
                        @endif
                        
                        @if($nfse->canBeCancelled())
                            <button type="button" class="btn btn-danger" onclick="showCancelModal()">
                                <i class="ti ti-x"></i> {{ __('Cancelar') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados do Tomador -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Tomador do Serviço') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('Nome/Razão Social') }}:</strong> {{ $nfse->tomador_nome }}</p>
                        <p><strong>{{ __('CPF/CNPJ') }}:</strong> {{ $nfse->tomador_cpf_cnpj }}</p>
                    </div>
                    <div class="col-md-6">
                        @if($nfse->tomador_email)
                            <p><strong>{{ __('Email') }}:</strong> {{ $nfse->tomador_email }}</p>
                        @endif
                        @if($nfse->tomador_telefone)
                            <p><strong>{{ __('Telefone') }}:</strong> {{ $nfse->tomador_telefone }}</p>
                        @endif
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
                <p><strong>{{ __('Descrição') }}:</strong></p>
                <p>{{ $nfse->descricao_servico }}</p>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <p><strong>{{ __('Item Lista') }}:</strong> {{ $nfse->item_lista_servico }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>{{ __('CNAE') }}:</strong> {{ $nfse->codigo_cnae ?? '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>{{ __('Alíquota ISS') }}:</strong> {{ number_format($nfse->aliquota_iss, 2, ',', '.') }}%</p>
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
                <table class="table">
                    <tr>
                        <td><strong>{{ __('Valor dos Serviços') }}:</strong></td>
                        <td class="text-end">R$ {{ number_format($nfse->valor_servicos, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Deduções') }}:</strong></td>
                        <td class="text-end">R$ {{ number_format($nfse->valor_deducoes, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Base de Cálculo') }}:</strong></td>
                        <td class="text-end">R$ {{ number_format($nfse->base_calculo, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Valor ISS') }}:</strong></td>
                        <td class="text-end">R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>{{ __('Valor Líquido') }}:</strong></td>
                        <td class="text-end"><strong>R$ {{ number_format($nfse->valor_liquido, 2, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Cancelar NFS-e') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="motivo_cancelamento">{{ __('Motivo do Cancelamento') }} <span class="text-danger">*</span></label>
                    <textarea id="motivo_cancelamento" class="form-control" rows="3" placeholder="Mínimo 15 caracteres"></textarea>
                    <small class="form-text text-muted">Informe o motivo do cancelamento (mínimo 15 caracteres)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Fechar') }}</button>
                <button type="button" class="btn btn-danger" onclick="cancelar()">{{ __('Cancelar NFS-e') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
function transmitir() {
    if (!confirm('Deseja transmitir esta NFS-e?')) {
        return;
    }

    fetch('{{ route("fiscalbr.nfse.transmitir", $nfse->id) }}', {
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
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erro ao transmitir NFS-e: ' + error);
    });
}

function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function cancelar() {
    const motivo = document.getElementById('motivo_cancelamento').value;
    
    if (motivo.length < 15) {
        alert('O motivo deve ter no mínimo 15 caracteres.');
        return;
    }

    fetch('{{ route("fiscalbr.nfse.cancelar", $nfse->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ motivo: motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erro ao cancelar NFS-e: ' + error);
    });
}
</script>
@endsection

