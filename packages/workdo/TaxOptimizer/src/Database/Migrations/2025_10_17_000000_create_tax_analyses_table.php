<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tax_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('cnae')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, error
            $table->json('input_data')->nullable(); // Receita, Custos, Folha de Pagamento
            $table->json('analysis_result')->nullable(); // Regime Ideal, Economia, Créditos
            $table->text('report_summary')->nullable();
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_analyses');
    }
};
