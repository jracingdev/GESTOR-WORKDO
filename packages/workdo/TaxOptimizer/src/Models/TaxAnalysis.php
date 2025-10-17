<?php

namespace Workdo\TaxOptimizer\Models;

use Illuminate\Database\Eloquent\Model;

class TaxAnalysis extends Model
{
    protected $table = 'tax_analyses';

    protected $fillable = [
        'company_id',
        'cnae',
        'status',
        'input_data',
        'analysis_result',
        'report_summary',
    ];

    protected $casts = [
        'input_data' => 'array',
        'analysis_result' => 'array',
    ];
}
