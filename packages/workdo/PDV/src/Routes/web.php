<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Workdo\PDV\Http\Controllers\PDVController;
use Workdo\PDV\Http\Controllers\ReportController;

Route::group(['middleware' => ['web', 'auth', 'verified','PlanModuleCheck:PDV']], function ()
{

        Route::get('dashboard/pdv',[PDVController::class, 'dashboard'])->name('pdv.dashboard');
        Route::post('pdv/setting/store', [PDVController::class, 'setting'])->name('pdv.setting.store');
        Route::resource('pdv', PDVController::class);
        Route::get('pdv-grid', [PDVController::class, 'grid'])->name('pdv.grid');
        Route::get('report/pdv', [PDVController::class, 'report'])->name('pdv.report');
        Route::get('search-products', [PDVController::class, 'searchProducts'])->name('search.products');
        Route::get('name-search-products', [PDVController::class, 'searchProductsByName'])->name('name.search.products');
        Route::post('warehouse-empty-cart', [PDVController::class, 'warehouseemptyCart'])->name('warehouse-empty-cart');
        Route::get('product-categories', [PDVController::class, 'getProductCategories'])->name('product.categories');
        Route::post('empty-cart', [PDVController::class, 'emptyCart']);
        Route::get('add-to-cart/{id}/{session}/{war_id}', [PDVController::class, 'addToCart']);
        Route::delete('remove-from-cart', [PDVController::class, 'removeFromCart']);
        Route::patch('update-cart', [PDVController::class, 'updateCart']);

        Route::get('pdv/data/store', [PDVController::class, 'store'])->name('pdv.data.store');

        // thermal print
        Route::get('printview/pdv', [PDVController::class, 'printView'])->name('pdv.printview');

        Route::post('/cartdiscount', [PDVController::class, 'cartdiscount'])->name('cartdiscount');

        Route::get('pdv/pdf/{id}', [PDVController::class, 'pdv'])->name('pdv.pdf');
        Route::post('/pdv/template/setting', [PDVController::class, 'savePDVTemplateSettings'])->name('pdv.template.setting');
        Route::get('pdv/preview/{template}/{color}', [PDVController::class, 'previewPDV'])->name('pdv.preview');


        //Reports
        Route::get('reports-daily-pdv', [ReportController::class, 'pdvDailyReport'])->name('report.daily.pdv');
        Route::get('reports-monthly-pdv', [ReportController::class, 'pdvMonthlyReport'])->name('report.monthly.pdv');
        Route::get('reports-pdv-vs-purchase', [ReportController::class, 'pdvVsPurchaseReport'])->name('report.pdv.vs.purchase');

        //pdv barcode
        Route::get('barcode/pdv', [PDVController::class, 'barcode'])->name('pdv.barcode');
        Route::get('setting/pdv', [PDVController::class, 'barcodeSetting'])->name('pdv.setting');
        Route::post('barcode/settings', [PDVController::class, 'BarcodesettingStore'])->name('barcode.setting');
        Route::get('print/pdv', [PDVController::class, 'printBarcode'])->name('pdv.print');
        Route::post('pdv/getproduct', [PDVController::class, 'getproduct'])->name('pdv.getproduct');
        Route::any('pdv-receipt', [PDVController::class, 'receipt'])->name('pdv.receipt');

});
