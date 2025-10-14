<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class NFSe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fiscalbr_nfse';

    protected $fillable = [
        'workspace_id',
        'numero_rps',
        'serie_rps',
        'numero_nfse',
        'codigo_verificacao',
        'data_emissao',
        'status',
        'tomador_nome',
        'tomador_cpf_cnpj',
        'tomador_inscricao_municipal',
        'tomador_endereco',
        'tomador_numero',
        'tomador_complemento',
        'tomador_bairro',
        'tomador_cidade',
        'tomador_uf',
        'tomador_cep',
        'tomador_email',
        'tomador_telefone',
        'descricao_servico',
        'codigo_servico',
        'codigo_cnae',
        'item_lista_servico',
        'codigo_tributacao_municipio',
        'valor_servicos',
        'valor_deducoes',
        'valor_pis',
        'valor_cofins',
        'valor_inss',
        'valor_ir',
        'valor_csll',
        'valor_iss',
        'valor_iss_retido',
        'valor_outras_retencoes',
        'base_calculo',
        'aliquota_iss',
        'valor_liquido',
        'desconto_incondicionado',
        'desconto_condicionado',
        'iss_retido',
        'exigibilidade_iss',
        'municipio_prestacao',
        'municipio_incidencia',
        'regime_especial_tributacao',
        'optante_simples_nacional',
        'incentivador_cultural',
        'natureza_operacao',
        'xml',
        'xml_path',
        'pdf_path',
        'prefeitura_provedor',
        'prefeitura_versao',
        'prefeitura_resposta',
        'protocolo',
        'data_cancelamento',
        'motivo_cancelamento',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_cancelamento' => 'datetime',
        'valor_servicos' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'valor_inss' => 'decimal:2',
        'valor_ir' => 'decimal:2',
        'valor_csll' => 'decimal:2',
        'valor_iss' => 'decimal:2',
        'valor_iss_retido' => 'decimal:2',
        'valor_outras_retencoes' => 'decimal:2',
        'base_calculo' => 'decimal:2',
        'aliquota_iss' => 'decimal:2',
        'valor_liquido' => 'decimal:2',
        'desconto_incondicionado' => 'decimal:2',
        'desconto_condicionado' => 'decimal:2',
    ];

    /**
     * Get the workspace that owns the NFS-e.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Check if NFS-e is authorized.
     */
    public function isAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }

    /**
     * Check if NFS-e is cancelled.
     */
    public function isCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    /**
     * Check if NFS-e can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'autorizada' && !$this->data_cancelamento;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'rascunho' => 'secondary',
            'rps_gerado' => 'info',
            'processando' => 'warning',
            'autorizada' => 'success',
            'cancelada' => 'danger',
            'erro' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'rascunho' => 'Rascunho',
            'rps_gerado' => 'RPS Gerado',
            'processando' => 'Processando',
            'autorizada' => 'Autorizada',
            'cancelada' => 'Cancelada',
            'erro' => 'Erro',
            default => 'Desconhecido'
        };
    }

    /**
     * Get exigibilidade ISS label.
     */
    public function getExigibilidadeIssLabelAttribute(): string
    {
        return match($this->exigibilidade_iss) {
            '1' => 'Exigível',
            '2' => 'Não incidência',
            '3' => 'Isenção',
            '4' => 'Exportação',
            '5' => 'Imunidade',
            '6' => 'Exigibilidade Suspensa por Decisão Judicial',
            '7' => 'Exigibilidade Suspensa por Processo Administrativo',
            default => 'Desconhecido'
        };
    }

    /**
     * Get natureza operacao label.
     */
    public function getNaturezaOperacaoLabelAttribute(): string
    {
        return match($this->natureza_operacao) {
            '1' => 'Tributação no município',
            '2' => 'Tributação fora do município',
            '3' => 'Isenção',
            '4' => 'Imune',
            '5' => 'Exigibilidade suspensa',
            '6' => 'Exportação',
            default => 'Desconhecido'
        };
    }
}

