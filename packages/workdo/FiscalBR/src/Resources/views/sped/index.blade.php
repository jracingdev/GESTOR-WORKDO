@extends('layouts.main')

@section('page-title')
    {{ __('SPED Fiscal') }}
@endsection

@section('page-breadcrumb')
    {{ __('Fiscal Brasileiro') }},
    {{ __('SPED Fiscal') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('fiscalbr.sped.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Gerar SPED') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{ __('Período') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Perfil') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Data Geração') }}</th>
                                <th>{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($speds as $sped)
                                <tr>
                                    <td>{{ $sped->periodo }}</td>
                                    <td>{{ $sped->tipo }}</td>
                                    <td>
                                        @if($sped->perfil === 'A')
                                            <span class="badge bg-primary">Perfil A (Completo)</span>
                                        @elseif($sped->perfil === 'B')
                                            <span class="badge bg-info">Perfil B (Simplificado)</span>
                                        @else
                                            <span class="badge bg-secondary">Perfil C (Lucro Presumido)</span>
                                        @endif
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>{{ $sped->data_geracao ? $sped->data_geracao->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('fiscalbr.sped.show', $sped->id) }}" class="btn btn-sm btn-info" title="{{ __('Visualizar') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('fiscalbr.sped.download', $sped->id) }}" class="btn btn-sm btn-success" title="{{ __('Download') }}">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="enviarContabilidade({{ $sped->id }})" title="{{ __('Enviar para Contabilidade') }}">
                                                <i class="ti ti-send"></i>
                                            </button>
                                            <form action="{{ route('fiscalbr.sped.destroy', $sped->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir este SPED?')" title="{{ __('Excluir') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('Nenhum SPED gerado ainda.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $speds->links() }}
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

