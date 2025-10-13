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
        Schema::create('fiscalbr_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->index();
            $table->string('nome');
            $table->text('certificado'); // Conteúdo do certificado criptografado
            $table->text('senha'); // Senha criptografada
            $table->date('validade');
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
        Schema::dropIfExists('fiscalbr_certificates');
    }
};

