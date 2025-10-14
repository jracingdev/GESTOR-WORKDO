<?php

use Illuminate\Support\Facades\Route;
use Workdo\FiscalBR\Http\Controllers\FiscalBRController;
use Workdo\FiscalBR\Http\Controllers\ConfigController;
use Workdo\FiscalBR\Http\Controllers\NFeController;
use Workdo\FiscalBR\Http\Controllers\NFCeController;
use Workdo\FiscalBR\Http\Controllers\SpedController;
use Workdo\FiscalBR\Http\Controllers\NFSeController;
use Workdo\FiscalBR\Http\Controllers\ReportController;
use Workdo\FiscalBR\Http\Controllers\BIController;

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
        Route::get('/create', [NFCeController::class, 'create'])->name('create');
        Route::post('/store', [NFCeController::class, 'store'])->name('store');
        Route::get('/{id}', [NFCeController::class, 'show'])->name('show');
        Route::post('/{id}/transmitir', [NFCeController::class, 'transmitir'])->name('transmitir');
        Route::get('/{id}/cupom', [NFCeController::class, 'cupom'])->name('cupom');
        Route::get('/{id}/xml', [NFCeController::class, 'downloadXml'])->name('xml');
        Route::get('/{id}/qrcode', [NFCeController::class, 'qrcode'])->name('qrcode');
    });
    
    // SPED Fiscal
    Route::prefix('fiscalbr/sped')->name('fiscalbr.sped.')->group(function () {
        Route::get('/', [SpedController::class, 'index'])->name('index');
        Route::get('/create', [SpedController::class, 'create'])->name('create');
        Route::post('/generate', [SpedController::class, 'generate'])->name('generate');
        Route::get('/{id}', [SpedController::class, 'show'])->name('show');
        Route::get('/{id}/download', [SpedController::class, 'download'])->name('download');
        Route::post('/{id}/enviar-contabilidade', [SpedController::class, 'enviarContabilidade'])->name('enviar_contabilidade');
        Route::delete('/{id}', [SpedController::class, 'destroy'])->name('destroy');
    });
    
    // NFS-e
    Route::prefix('fiscalbr/nfse')->name('fiscalbr.nfse.')->group(function () {
        Route::get('/', [NFSeController::class, 'index'])->name('index');
        Route::get('/create', [NFSeController::class, 'create'])->name('create');
        Route::post('/store', [NFSeController::class, 'store'])->name('store');
        Route::get('/{id}', [NFSeController::class, 'show'])->name('show');
        Route::post('/{id}/transmitir', [NFSeController::class, 'transmitir'])->name('transmitir');
        Route::post('/{id}/cancelar', [NFSeController::class, 'cancelar'])->name('cancelar');
        Route::get('/{id}/consultar', [NFSeController::class, 'consultar'])->name('consultar');
        Route::get('/{id}/xml', [NFSeController::class, 'downloadXml'])->name('xml');
        Route::get('/{id}/pdf', [NFSeController::class, 'pdf'])->name('pdf');
    });
    
    // Relatórios Fiscais
    Route::prefix('fiscalbr/reports')->name('fiscalbr.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/nfe', [ReportController::class, 'nfe'])->name('nfe');
        Route::get('/nfce', [ReportController::class, 'nfce'])->name('nfce');
        Route::get('/sped', [ReportController::class, 'sped'])->name('sped');
        Route::get('/nfse', [ReportController::class, 'nfse'])->name('nfse');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
    });
    
    // Business Intelligence Fiscal
    Route::prefix('fiscalbr/bi')->name('fiscalbr.bi.')->group(function () {
        Route::get('/', [BIController::class, 'index'])->name('index');
        Route::post('/export', [BIController::class, 'export'])->name('export');
    });
});

