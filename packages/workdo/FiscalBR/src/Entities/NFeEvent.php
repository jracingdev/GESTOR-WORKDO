<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NFeEvent extends Model
{
    use HasFactory;

    protected $table = 'fiscalbr_nfe_events';

    protected $fillable = [
        'workspace_id',
        'nfe_id',
        'tipo',
        'sequencia',
        'protocolo',
        'justificativa',
        'correcao',
        'xml_evento',
        'xml_retorno',
        'status',
        'codigo_status',
        'mensagem',
        'data_evento',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
    ];

    /**
     * Get the workspace that owns the event.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Get the NFe that owns the event.
     */
    public function nfe()
    {
        return $this->belongsTo(NFe::class, 'nfe_id');
    }

    /**
     * Check if event is registered.
     */
    public function isRegistrado(): bool
    {
        return $this->status === 'registrado';
    }

    /**
     * Get next sequence number for CC-e.
     */
    public static function getNextSequence(int $nfeId, string $tipo): int
    {
        $lastEvent = self::where('nfe_id', $nfeId)
            ->where('tipo', $tipo)
            ->orderBy('sequencia', 'desc')
            ->first();

        return $lastEvent ? $lastEvent->sequencia + 1 : 1;
    }
}

