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
        Schema::create('fiscalbr_sefaz_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->index();
            $table->unsignedBigInteger('nfe_id')->nullable()->index();
            $table->string('operacao', 50); // autorizacao, consulta, cancelamento, etc
            $table->string('uf', 2);
            $table->enum('ambiente', ['producao', 'homologacao']);
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->string('status_code', 10)->nullable();
            $table->string('mensagem')->nullable();
            $table->integer('tempo_resposta')->nullable(); // em milissegundos
            $table->timestamps();
            
            $table->index(['workspace_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_sefaz_logs');
    }
};

