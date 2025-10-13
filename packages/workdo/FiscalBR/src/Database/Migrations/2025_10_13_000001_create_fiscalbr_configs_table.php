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
        Schema::create('fiscalbr_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->index();
            $table->string('cnpj', 18);
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('inscricao_estadual', 20)->nullable();
            $table->string('inscricao_municipal', 20)->nullable();
            $table->string('cnae', 10)->nullable();
            $table->enum('regime_tributario', ['simples_nacional', 'lucro_presumido', 'lucro_real'])->default('simples_nacional');
            $table->enum('ambiente', ['producao', 'homologacao'])->default('homologacao');
            $table->string('serie_nfe', 3)->default('1');
            $table->integer('numero_nfe')->default(1);
            $table->string('serie_nfce', 3)->default('1');
            $table->integer('numero_nfce')->default(1);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique(['workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_configs');
    }
};

