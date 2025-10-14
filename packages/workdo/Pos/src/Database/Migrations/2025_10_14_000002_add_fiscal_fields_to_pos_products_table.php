<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            // Campos fiscais para integração com Módulo Fiscal Brasileiro
            $table->string('cfop', 4)->nullable()->after('description'); // Código Fiscal de Operações e Prestações
            $table->string('ncm', 8)->nullable()->after('cfop'); // Nomenclatura Comum do Mercosul
            $table->string('cest', 7)->nullable()->after('ncm'); // Código Especificador da Substituição Tributária
            $table->string('cst_icms', 3)->nullable()->after('cest'); // Código de Situação Tributária do ICMS
            $table->string('csosn', 4)->nullable()->after('cst_icms'); // Código de Situação da Operação no Simples Nacional
            $table->decimal('aliquota_icms', 5, 2)->default(0)->after('csosn'); // Alíquota do ICMS
            $table->decimal('valor_icms', 15, 2)->default(0)->after('aliquota_icms'); // Valor do ICMS
            $table->decimal('base_calculo_icms', 15, 2)->default(0)->after('valor_icms'); // Base de cálculo do ICMS
            $table->string('cst_pis', 2)->nullable()->after('base_calculo_icms'); // CST do PIS
            $table->decimal('aliquota_pis', 5, 2)->default(0)->after('cst_pis'); // Alíquota do PIS
            $table->decimal('valor_pis', 15, 2)->default(0)->after('aliquota_pis'); // Valor do PIS
            $table->string('cst_cofins', 2)->nullable()->after('valor_pis'); // CST do COFINS
            $table->decimal('aliquota_cofins', 5, 2)->default(0)->after('cst_cofins'); // Alíquota do COFINS
            $table->decimal('valor_cofins', 15, 2)->default(0)->after('aliquota_cofins'); // Valor do COFINS
            $table->string('unidade_comercial', 10)->default('UN')->after('valor_cofins'); // Unidade comercial (UN, KG, LT, etc)
            $table->string('codigo_ean', 14)->nullable()->after('unidade_comercial'); // Código de barras EAN
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};

