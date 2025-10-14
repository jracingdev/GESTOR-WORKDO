<?php

namespace Workdo\Pos\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type',
        'product_id',
        'pos_id',
        'quantity',
        'tax',
        'discount',
        'total',
        'workspace',
        // Campos fiscais
        'cfop',
        'ncm',
        'cest',
        'cst_icms',
        'csosn',
        'aliquota_icms',
        'valor_icms',
        'base_calculo_icms',
        'cst_pis',
        'aliquota_pis',
        'valor_pis',
        'cst_cofins',
        'aliquota_cofins',
        'valor_cofins',
        'unidade_comercial',
        'codigo_ean',
    ];

    public function product(){
        return $this->hasOne(\Workdo\ProductService\Entities\ProductService::class, 'id', 'product_id');
    }

}
