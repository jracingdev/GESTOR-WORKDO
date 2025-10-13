<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class NFe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fiscalbr_nfe';

    protected $fillable = [
        'workspace_id',
        'tipo',
        'chave_acesso',
        'numero',
        'serie',
        'modelo',
        'data_emissao',
        'hora_emissao',
        'destinatario_cpf_cnpj',
        'destinatario_nome',
        'destinatario_ie',
        'destinatario_endereco',
        'destinatario_cidade',
        'destinatario_uf',
        'destinatario_cep',
        'valor_produtos',
        'valor_frete',
        'valor_desconto',
        'valor_icms',
        'valor_ipi',
        'valor_pis',
        'valor_cofins',
        'valor_total',
        'status',
        'protocolo',
        'motivo_rejeicao',
        'data_autorizacao',
        'data_cancelamento',
        'xml_enviado',
        'xml_autorizado',
        'xml_cancelamento',
        'invoice_id',
        'pos_sale_id',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_autorizacao' => 'datetime',
        'data_cancelamento' => 'datetime',
        'valor_produtos' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_icms' => 'decimal:2',
        'valor_ipi' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    /**
     * Get the workspace that owns the NFe.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Get the items for the NFe.
     */
    public function items()
    {
        return $this->hasMany(NFeItem::class, 'nfe_id');
    }

    /**
     * Get the invoice associated with the NFe.
     */
    public function invoice()
    {
        return $this->belongsTo(\Workdo\Account\Entities\Invoice::class, 'invoice_id');
    }

    /**
     * Check if NFe is authorized.
     */
    public function isAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }

    /**
     * Check if NFe can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->isAutorizada() && $this->data_autorizacao->diffInHours(now()) <= 24;
    }

    /**
     * Get formatted chave de acesso.
     */
    public function getChaveFormatadaAttribute(): string
    {
        if (!$this->chave_acesso) {
            return '';
        }
        return chunk_split($this->chave_acesso, 4, ' ');
    }
}

