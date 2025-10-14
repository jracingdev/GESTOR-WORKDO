<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FiscalConfig extends Model
{
    use HasFactory;

    protected $table = 'fiscalbr_configs';

    protected $fillable = [
        'workspace_id',
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'inscricao_estadual',
        'inscricao_municipal',
        'cnae',
        'regime_tributario',
        'ambiente',
        'serie_nfe',
        'numero_nfe',
        'ultimo_numero_nfe',
        'serie_nfce',
        'numero_nfce',
        'ultimo_numero_nfce',
        'csc',
        'csc_id',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Get the workspace that owns the config.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Get next NF-e number.
     */
    public function getNextNFeNumber(): int
    {
        $numero = $this->numero_nfe;
        $this->increment('numero_nfe');
        return $numero;
    }

    /**
     * Get next NFC-e number.
     */
    public function getNextNFCeNumber(): int
    {
        $numero = $this->numero_nfce;
        $this->increment('numero_nfce');
        return $numero;
    }
}

