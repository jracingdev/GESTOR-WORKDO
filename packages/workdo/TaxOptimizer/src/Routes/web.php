<?php

use Illuminate\Support\Facades\Route;
use Workdo\TaxOptimizer\Http\Controllers\TaxOptimizerController;

Route::group(['middleware' => ['web', 'auth', 'verified'], 'prefix' => 'tax-optimizer', 'as' => 'taxoptimizer.'], function () {
    // Análise
    Route::get('/', [TaxOptimizerController::class, 'index'])->name('index');
    Route::post('/upload', [TaxOptimizerController::class, 'upload'])->name('upload');
    Route::get('/analyze', [TaxOptimizerController::class, 'analyze'])->name('analyze');

    // Regras (Simulação)
    Route::get('/rules', [TaxOptimizerController::class, 'rulesIndex'])->name('rules.index');
    Route::post('/rules', [TaxOptimizerController::class, 'rulesStore'])->name('rules.store');
});
