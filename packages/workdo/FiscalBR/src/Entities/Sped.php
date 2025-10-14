<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sped extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fiscalbr_sped';

    protected $fillable = [
        'workspace_id',
        'ano',
        'mes',
        'tipo',
        'perfil',
        'status',
        'arquivo',
        'nome_arquivo',
        'erros_validacao',
        'data_geracao',
        'data_validacao',
        'data_transmissao',
        'recibo_transmissao',
    ];

    protected $casts = [
        'data_geracao' => 'datetime',
        'data_validacao' => 'datetime',
        'data_transmissao' => 'datetime',
    ];

    /**
     * Get the workspace that owns the SPED.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Get period description.
     */
    public function getPeriodoAttribute(): string
    {
        return sprintf('%02d/%d', $this->mes, $this->ano);
    }

    /**
     * Check if SPED is validated.
     */
    public function isValidado(): bool
    {
        return $this->status === 'validado';
    }

    /**
     * Check if SPED is transmitted.
     */
    public function isTransmitido(): bool
    {
        return $this->status === 'transmitido';
    }
}

