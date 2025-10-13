<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SefazLog extends Model
{
    use HasFactory;

    protected $table = 'fiscalbr_sefaz_logs';

    protected $fillable = [
        'workspace_id',
        'nfe_id',
        'operacao',
        'uf',
        'ambiente',
        'request',
        'response',
        'status_code',
        'mensagem',
        'tempo_resposta',
    ];

    protected $casts = [
        'tempo_resposta' => 'integer',
    ];

    /**
     * Get the workspace that owns the log.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Get the NFe associated with the log.
     */
    public function nfe()
    {
        return $this->belongsTo(NFe::class, 'nfe_id');
    }
}

