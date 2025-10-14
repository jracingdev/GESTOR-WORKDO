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
        Schema::create('fiscalbr_sped', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->integer('ano');
            $table->integer('mes');
            $table->string('tipo', 20)->default('EFD_ICMS_IPI'); // EFD_ICMS_IPI, EFD_CONTRIBUICOES
            $table->enum('perfil', ['A', 'B', 'C'])->default('A'); // A=Completo, B=Simplificado, C=Lucro Presumido
            $table->enum('status', ['gerando', 'gerado', 'validado', 'transmitido', 'erro'])->default('gerando');
            $table->text('arquivo')->nullable(); // Conteúdo do arquivo SPED
            $table->string('nome_arquivo', 255)->nullable();
            $table->text('erros_validacao')->nullable();
            $table->dateTime('data_geracao')->nullable();
            $table->dateTime('data_validacao')->nullable();
            $table->dateTime('data_transmissao')->nullable();
            $table->string('recibo_transmissao', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'ano', 'mes']);
            $table->unique(['workspace_id', 'ano', 'mes', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_sped');
    }
};

