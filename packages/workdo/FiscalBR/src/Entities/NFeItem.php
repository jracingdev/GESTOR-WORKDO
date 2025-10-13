<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NFeItem extends Model
{
    use HasFactory;

    protected $table = 'fiscalbr_nfe_items';

    protected $fillable = [
        'nfe_id',
        'numero_item',
        'codigo_produto',
        'descricao',
        'ncm',
        'cest',
        'cfop',
        'unidade',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'valor_desconto',
        'icms_origem',
        'icms_cst',
        'icms_base_calculo',
        'icms_aliquota',
        'icms_valor',
        'ipi_cst',
        'ipi_base_calculo',
        'ipi_aliquota',
        'ipi_valor',
        'pis_cst',
        'pis_base_calculo',
        'pis_aliquota',
        'pis_valor',
        'cofins_cst',
        'cofins_base_calculo',
        'cofins_aliquota',
        'cofins_valor',
    ];

    protected $casts = [
        'quantidade' => 'decimal:4',
        'valor_unitario' => 'decimal:4',
        'valor_total' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'icms_base_calculo' => 'decimal:2',
        'icms_aliquota' => 'decimal:2',
        'icms_valor' => 'decimal:2',
        'ipi_base_calculo' => 'decimal:2',
        'ipi_aliquota' => 'decimal:2',
        'ipi_valor' => 'decimal:2',
        'pis_base_calculo' => 'decimal:2',
        'pis_aliquota' => 'decimal:4',
        'pis_valor' => 'decimal:2',
        'cofins_base_calculo' => 'decimal:2',
        'cofins_aliquota' => 'decimal:4',
        'cofins_valor' => 'decimal:2',
    ];

    /**
     * Get the NFe that owns the item.
     */
    public function nfe()
    {
        return $this->belongsTo(NFe::class, 'nfe_id');
    }
}

