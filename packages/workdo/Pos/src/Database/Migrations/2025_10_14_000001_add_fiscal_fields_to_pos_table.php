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
        Schema::table('pos', function (Blueprint $table) {
            // Campos fiscais para integração com Módulo Fiscal Brasileiro
            $table->unsignedBigInteger('nfce_id')->nullable()->after('status'); // ID da NFC-e gerada
            $table->enum('fiscal_status', ['pendente', 'processando', 'emitida', 'erro', 'sem_emissao'])->default('pendente')->after('nfce_id');
            $table->boolean('emitir_nfce')->default(true)->after('fiscal_status'); // Flag para emitir NFC-e automaticamente
            $table->text('fiscal_observacao')->nullable()->after('emitir_nfce'); // Observações fiscais
            $table->dateTime('fiscal_emissao_data')->nullable()->after('fiscal_observacao'); // Data/hora da emissão fiscal
            $table->string('fiscal_numero_nfce', 20)->nullable()->after('fiscal_emissao_data'); // Número da NFC-e
            $table->string('fiscal_chave_acesso', 44)->nullable()->after('fiscal_numero_nfce'); // Chave de acesso da NFC-e
            $table->text('fiscal_erro_mensagem')->nullable()->after('fiscal_chave_acesso'); // Mensagem de erro (se houver)
            
            // Índices para melhor performance
            $table->index('nfce_id');
            $table->index('fiscal_status');
            $table->index('fiscal_numero_nfce');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropIndex(['nfce_id']);
            $table->dropIndex(['fiscal_status']);
            $table->dropIndex(['fiscal_numero_nfce']);
            
            $table->dropColumn([
                'nfce_id',
                'fiscal_status',
                'emitir_nfce',
                'fiscal_observacao',
                'fiscal_emissao_data',
                'fiscal_numero_nfce',
                'fiscal_chave_acesso',
                'fiscal_erro_mensagem',
            ]);
        });
    }
};

