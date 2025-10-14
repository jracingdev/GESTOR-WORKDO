<?php

use Illuminate\Support\Facades\Route;
use Workdo\FiscalBR\Http\Controllers\FiscalBRController;
use Workdo\FiscalBR\Http\Controllers\ConfigController;
use Workdo\FiscalBR\Http\Controllers\NFeController;
use Workdo\FiscalBR\Http\Controllers\NFCeController;

/*
|--------------------------------------------------------------------------
| Web Routes - Módulo Fiscal Brasileiro
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web', 'auth', 'verified', 'PlanModuleCheck:FiscalBR']], function () {
    
    // Dashboard Fiscal
    Route::get('fiscalbr/dashboard', [FiscalBRController::class, 'index'])->name('fiscalbr.dashboard');
    
    // Configurações Fiscais
    Route::prefix('fiscalbr/config')->name('fiscalbr.config.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/empresa', [ConfigController::class, 'updateEmpresa'])->name('empresa.update');
        Route::post('/certificado', [ConfigController::class, 'uploadCertificado'])->name('certificado.upload');
        Route::post('/certificado/test', [ConfigController::class, 'testCertificado'])->name('certificado.test');
    });
    
    // NF-e
    Route::prefix('fiscalbr/nfe')->name('fiscalbr.nfe.')->group(function () {
        Route::get('/', [NFeController::class, 'index'])->name('index');
        Route::get('/create', [NFeController::class, 'create'])->name('create');
        Route::post('/store', [NFeController::class, 'store'])->name('store');
        Route::get('/{id}', [NFeController::class, 'show'])->name('show');
        Route::post('/{id}/transmitir', [NFeController::class, 'transmitir'])->name('transmitir');
        Route::post('/{id}/cancelar', [NFeController::class, 'cancelar'])->name('cancelar');
        Route::post('/{id}/carta-correcao', [NFeController::class, 'cartaCorrecao'])->name('carta_correcao');
        Route::get('/{id}/consultar', [NFeController::class, 'consultar'])->name('consultar');
        Route::get('/{id}/danfe', [NFeController::class, 'danfe'])->name('danfe');
        Route::get('/{id}/xml', [NFeController::class, 'downloadXml'])->name('xml');
    });
    
    // NFC-e
    Route::prefix('fiscalbr/nfce')->name('fiscalbr.nfce.')->group(function () {
        Route::get('/', [NFCeController::class, 'index'])->name('index');
        Route::post('/emitir', [NFCeController::class, 'emitir'])->name('emitir');
        Route::get('/{id}/danfe', [NFCeController::class, 'danfe'])->name('danfe');
    });
});

