@extends('layouts.main')

@section('page-title')
    {{ __('NFC-e') }} #{{ $nfce->numero }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    <a href="{{ route('fiscalbr.nfce.index') }}">{{ __('NFC-e') }}</a>,
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
                        <h4>NFC-e Nº {{ $nfce->numero }} - Série {{ $nfce->serie }}</h4>
                        <p class="text-muted mb-0">
                            <strong>Status:</strong> 
                            @if($nfce->status === 'rascunho')
                                <span class="badge bg-secondary">Rascunho</span>
                            @elseif($nfce->status === 'processando')
                                <span class="badge bg-warning">Processando</span>
                            @elseif($nfce->status === 'autorizada')
                                <span class="badge bg-success">Autorizada</span>
                            @elseif($nfce->status === 'rejeitada')
                                <span class="badge bg-danger">Rejeitada</span>
                            @elseif($nfce->status === 'cancelada')
                                <span class="badge bg-dark">Cancelada</span>
                            @endif
                        </p>
                        @if($nfce->chave_acesso)
                            <p class="text-muted mb-0"><strong>Chave:</strong> {{ $nfce->chave_formatada }}</p>
                        @endif
                        @if($nfce->protocolo)
                            <p class="text-muted mb-0"><strong>Protocolo:</strong> {{ $nfce->protocolo }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        @if($nfce->status === 'rascunho')
                            <button type="button" class="btn btn-primary" onclick="transmitirNFCe({{ $nfce->id }})">
                                <i class="ti ti-send"></i> {{ __('Transmitir') }}
                            </button>
                        @endif
                        @if($nfce->status === 'autorizada')
                            <div class="btn-group" role="group">
                                <a href="{{ route('fiscalbr.nfce.cupom', $nfce->id) }}" class="btn btn-info" target="_blank">
                                    <i class="ti ti-receipt"></i> {{ __('Cupom') }}
                                </a>
                                <a href="{{ route('fiscalbr.nfce.xml', $nfce->id) }}" class="btn btn-secondary">
                                    <i class="ti ti-file-code"></i> {{ __('XML') }}
                                </a>
                                <button type="button" class="btn btn-success" onclick="showQRCode()">
                                    <i class="ti ti-qrcode"></i> {{ __('QR Code') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Card (hidden by default) -->
        @if($nfce->status === 'autorizada' && $nfce->qr_code_url)
            <div class="card mt-3" id="qrCodeCard" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5>{{ __('QR Code para Consulta') }}</h5>
                </div>
                <div class="card-body text-center">
                    <div id="qrcode" class="mb-3"></div>
                    <p class="text-muted">{{ __('Escaneie o QR Code para consultar a NFC-e') }}</p>
                    <button type="button" class="btn btn-outline-primary" onclick="printQRCode()">
                        <i class="ti ti-printer"></i> {{ __('Imprimir QR Code') }}
                    </button>
                </div>
            </div>
        @endif

        <!-- Consumidor -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Consumidor') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('Nome') }}:</strong> {{ $nfce->destinatario_nome ?? 'CONSUMIDOR' }}</p>
                        @if($nfce->destinatario_cpf_cnpj)
                            <p><strong>{{ __('CPF/CNPJ') }}:</strong> {{ $nfce->destinatario_cpf_cnpj }}</p>
                        @endif
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
                @if($nfce->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Descrição') }}</th>
                                    <th>{{ __('Qtd') }}</th>
                                    <th>{{ __('Valor Unit.') }}</th>
                                    <th>{{ __('Valor Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nfce->items as $item)
                                    <tr>
                                        <td>{{ $item->numero_item }}</td>
                                        <td>{{ $item->codigo_produto }}</td>
                                        <td>{{ $item->descricao }}</td>
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
                        <p><strong>{{ __('Valor dos Produtos') }}:</strong> R$ {{ number_format($nfce->valor_produtos, 2, ',', '.') }}</p>
                        <p><strong>{{ __('Valor do Desconto') }}:</strong> R$ {{ number_format($nfce->valor_desconto, 2, ',', '.') }}</p>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h4><strong>{{ __('Valor Total da NFC-e') }}:</strong> R$ {{ number_format($nfce->valor_total, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($nfce->motivo_rejeicao)
            <!-- Motivo de Rejeição -->
            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5>{{ __('Motivo da Rejeição') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ $nfce->motivo_rejeicao }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
let qrCodeGenerated = false;

function transmitirNFCe(nfceId) {
    if (!confirm('Deseja realmente transmitir esta NFC-e para a SEFAZ?')) {
        return;
    }

    fetch(`/fiscalbr/nfce/${nfceId}/transmitir`, {
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
        alert('Erro ao transmitir NFC-e: ' + error);
    });
}

function showQRCode() {
    const qrCodeCard = document.getElementById('qrCodeCard');
    qrCodeCard.style.display = 'block';
    
    if (!qrCodeGenerated) {
        const qrCodeURL = '{{ $nfce->qr_code_url ?? '' }}';
        
        if (qrCodeURL) {
            new QRCode(document.getElementById('qrcode'), {
                text: qrCodeURL,
                width: 256,
                height: 256,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
            qrCodeGenerated = true;
        }
    }
    
    // Scroll to QR Code
    qrCodeCard.scrollIntoView({ behavior: 'smooth' });
}

function printQRCode() {
    const qrCodeCard = document.getElementById('qrCodeCard');
    const printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>QR Code - NFC-e {{ $nfce->numero }}</title>');
    printWindow.document.write('<style>body { text-align: center; padding: 20px; } h3 { margin-bottom: 20px; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h3>NFC-e Nº {{ $nfce->numero }} - Série {{ $nfce->serie }}</h3>');
    printWindow.document.write(document.getElementById('qrcode').innerHTML);
    printWindow.document.write('<p>Chave: {{ $nfce->chave_formatada }}</p>');
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection

