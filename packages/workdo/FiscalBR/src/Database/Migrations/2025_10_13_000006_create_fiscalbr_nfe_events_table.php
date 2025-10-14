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
        Schema::create('fiscalbr_nfe_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->index();
            $table->unsignedBigInteger('nfe_id')->index();
            $table->enum('tipo', ['cancelamento', 'carta_correcao', 'inutilizacao'])->index();
            $table->integer('sequencia')->default(1);
            $table->string('protocolo', 20)->nullable();
            $table->text('justificativa')->nullable();
            $table->text('correcao')->nullable();
            $table->longText('xml_evento')->nullable();
            $table->longText('xml_retorno')->nullable();
            $table->enum('status', ['processando', 'registrado', 'rejeitado'])->default('processando');
            $table->string('codigo_status', 10)->nullable();
            $table->text('mensagem')->nullable();
            $table->timestamp('data_evento')->nullable();
            $table->timestamps();
            
            $table->foreign('nfe_id')->references('id')->on('fiscalbr_nfe')->onDelete('cascade');
            $table->index(['workspace_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_nfe_events');
    }
};

