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
        Schema::create('fiscalbr_nfe_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nfe_id')->index();
            $table->integer('numero_item');
            
            // Produto
            $table->string('codigo_produto', 60);
            $table->string('descricao');
            $table->string('ncm', 8);
            $table->string('cest', 7)->nullable();
            $table->string('cfop', 4);
            $table->string('unidade', 6);
            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            
            // ICMS
            $table->string('icms_origem', 1);
            $table->string('icms_cst', 3);
            $table->decimal('icms_base_calculo', 15, 2)->default(0);
            $table->decimal('icms_aliquota', 5, 2)->default(0);
            $table->decimal('icms_valor', 15, 2)->default(0);
            
            // IPI
            $table->string('ipi_cst', 2)->nullable();
            $table->decimal('ipi_base_calculo', 15, 2)->default(0);
            $table->decimal('ipi_aliquota', 5, 2)->default(0);
            $table->decimal('ipi_valor', 15, 2)->default(0);
            
            // PIS
            $table->string('pis_cst', 2);
            $table->decimal('pis_base_calculo', 15, 2)->default(0);
            $table->decimal('pis_aliquota', 5, 4)->default(0);
            $table->decimal('pis_valor', 15, 2)->default(0);
            
            // COFINS
            $table->string('cofins_cst', 2);
            $table->decimal('cofins_base_calculo', 15, 2)->default(0);
            $table->decimal('cofins_aliquota', 5, 4)->default(0);
            $table->decimal('cofins_valor', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('nfe_id')->references('id')->on('fiscalbr_nfe')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_nfe_items');
    }
};

