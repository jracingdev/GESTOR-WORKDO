<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFiscalFieldsToProductServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('product_services'))
        {
            Schema::table('product_services', function (Blueprint $table) {
                if (!Schema::hasColumn('product_services', 'ncm')) {
                    $table->string('ncm', 10)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('product_services', 'cest')) {
                    $table->string('cest', 10)->nullable()->after('ncm');
                }
                if (!Schema::hasColumn('product_services', 'cfop')) {
                    $table->string('cfop', 5)->nullable()->after('cest');
                }
                if (!Schema::hasColumn('product_services', 'cst_icms')) {
                    $table->string('cst_icms', 3)->nullable()->after('cfop');
                }
                if (!Schema::hasColumn('product_services', 'cst_ipi')) {
                    $table->string('cst_ipi', 2)->nullable()->after('cst_icms');
                }
                if (!Schema::hasColumn('product_services', 'cst_pis')) {
                    $table->string('cst_pis', 2)->nullable()->after('cst_ipi');
                }
                if (!Schema::hasColumn('product_services', 'cst_cofins')) {
                    $table->string('cst_cofins', 2)->nullable()->after('cst_pis');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('product_services'))
        {
            Schema::table('product_services', function (Blueprint $table) {
                if (Schema::hasColumn('product_services', 'ncm')) {
                    $table->dropColumn('ncm');
                }
                if (Schema::hasColumn('product_services', 'cest')) {
                    $table->dropColumn('cest');
                }
                if (Schema::hasColumn('product_services', 'cfop')) {
                    $table->dropColumn('cfop');
                }
                if (Schema::hasColumn('product_services', 'cst_icms')) {
                    $table->dropColumn('cst_icms');
                }
                if (Schema::hasColumn('product_services', 'cst_ipi')) {
                    $table->dropColumn('cst_ipi');
                }
                if (Schema::hasColumn('product_services', 'cst_pis')) {
                    $table->dropColumn('cst_pis');
                }
                if (Schema::hasColumn('product_services', 'cst_cofins')) {
                    $table->dropColumn('cst_cofins');
                }
            });
        }
    }
}

