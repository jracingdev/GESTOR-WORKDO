@extends('layouts.main')

@section('page-title')
    {{ __('NF-e') }} #{{ $nfe->numero }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.nfe.index') }}">{{ __('NF-e') }}</a>,
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
                        <h4>NF-e Nº {{ $nfe->numero }} - Série {{ $nfe->serie }}</h4>
                        <p class="text-muted mb-0">
                            <strong>Status:</strong> 
                            @if($nfe->status === 'rascunho')
                                <span class="badge bg-secondary">Rascunho</span>
                            @elseif($nfe->status === 'processando')
                                <span class="badge bg-warning">Processando</span>
                            @elseif($nfe->status === 'autorizada')
                                <span class="badge bg-success">Autorizada</span>
                            @elseif($nfe->status === 'rejeitada')
                                <span class="badge bg-danger">Rejeitada</span>
                            @elseif($nfe->status === 'cancelada')
                                <span class="badge bg-dark">Cancelada</span>
                            @endif
                        </p>
                        @if($nfe->chave_acesso)
                            <p class="text-muted mb-0"><strong>Chave:</strong> {{ $nfe->chave_formatada }}</p>
                        @endif
                        @if($nfe->protocolo)
                            <p class="text-muted mb-0"><strong>Protocolo:</strong> {{ $nfe->protocolo }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        @if($nfe->status === 'rascunho')
                            <button type="button" class="btn btn-primary" onclick="transmitirNFe({{ $nfe->id }})">
                                <i class="ti ti-send"></i> {{ __('Transmitir') }}
                            </button>
                        @endif
                        @if($nfe->status === 'autorizada')
                            <a href="{{ route('fiscalbr.nfe.danfe', $nfe->id) }}" class="btn btn-info" target="_blank">
                                <i class="ti ti-file-download"></i> {{ __('DANFE') }}
                            </a>
                            <a href="{{ route('fiscalbr.nfe.xml', $nfe->id) }}" class="btn btn-secondary">
                                <i class="ti ti-file-code"></i> {{ __('XML') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Destinatário -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Destinatário') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('Nome/Razão Social') }}:</strong> {{ $nfe->destinatario_nome }}</p>
                        <p><strong>{{ __('CPF/CNPJ') }}:</strong> {{ $nfe->destinatario_cpf_cnpj }}</p>
                    </div>
                    <div class="col-md-6">
                        @if($nfe->destinatario_ie)
                            <p><strong>{{ __('Inscrição Estadual') }}:</strong> {{ $nfe->destinatario_ie }}</p>
                        @endif
                        <p><strong>{{ __('UF') }}:</strong> {{ $nfe->destinatario_uf }}</p>
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
                @if($nfe->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Descrição') }}</th>
                                    <th>{{ __('NCM') }}</th>
                                    <th>{{ __('Qtd') }}</th>
                                    <th>{{ __('Valor Unit.') }}</th>
                                    <th>{{ __('Valor Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nfe->items as $item)
                                    <tr>
                                        <td>{{ $item->numero_item }}</td>
                                        <td>{{ $item->codigo_produto }}</td>
                                        <td>{{ $item->descricao }}</td>
                                        <td>{{ $item->ncm }}</td>
                                        <td>{{ $item->quantidade }} {{ $item->unidade }}</td>
                                        <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        {{ __('Nenhum produto/serviço adicionado ainda.') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Totais -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Valores Totais') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('Valor dos Produtos') }}:</strong> R$ {{ number_format($nfe->valor_produtos, 2, ',', '.') }}</p>
                        <p><strong>{{ __('Valor do Frete') }}:</strong> R$ {{ number_format($nfe->valor_frete, 2, ',', '.') }}</p>
                        <p><strong>{{ __('Valor do Desconto') }}:</strong> R$ {{ number_format($nfe->valor_desconto, 2, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>{{ __('ICMS') }}:</strong> R$ {{ number_format($nfe->valor_icms, 2, ',', '.') }}</p>
                        <p><strong>{{ __('IPI') }}:</strong> R$ {{ number_format($nfe->valor_ipi, 2, ',', '.') }}</p>
                        <p><strong>{{ __('PIS') }}:</strong> R$ {{ number_format($nfe->valor_pis, 2, ',', '.') }}</p>
                        <p><strong>{{ __('COFINS') }}:</strong> R$ {{ number_format($nfe->valor_cofins, 2, ',', '.') }}</p>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h4><strong>{{ __('Valor Total da NF-e') }}:</strong> R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($nfe->motivo_rejeicao)
            <!-- Motivo de Rejeição -->
            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5>{{ __('Motivo da Rejeição') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ $nfe->motivo_rejeicao }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function transmitirNFe(nfeId) {
    if (!confirm('Deseja realmente transmitir esta NF-e para a SEFAZ?')) {
        return;
    }

    fetch(`/fiscalbr/nfe/${nfeId}/transmitir`, {
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
        alert('Erro ao transmitir NF-e: ' + error);
    });
}
</script>
@endsection

