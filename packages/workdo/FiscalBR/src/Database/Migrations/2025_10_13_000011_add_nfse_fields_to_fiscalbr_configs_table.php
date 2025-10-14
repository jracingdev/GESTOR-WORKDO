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
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            // NFS-e Configuration
            if (!Schema::hasColumn('fiscalbr_configs', 'inscricao_municipal')) {
                $table->string('inscricao_municipal', 20)->nullable();
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'codigo_municipio')) {
                $table->string('codigo_municipio', 7)->nullable(); // Código IBGE
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_serie_rps')) {
                $table->string('nfse_serie_rps', 5)->default('1');
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_ultimo_numero_rps')) {
                $table->integer('nfse_ultimo_numero_rps')->default(0);
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_provedor')) {
                $table->string('nfse_provedor', 50)->nullable(); // ABRASF, GINFES, etc
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_versao')) {
                $table->string('nfse_versao', 10)->nullable(); // Versão do webservice
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_usuario')) {
                $table->string('nfse_usuario', 100)->nullable(); // Usuário da prefeitura
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_senha')) {
                $table->text('nfse_senha')->nullable(); // Senha (criptografada)
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_regime_especial')) {
                $table->string('nfse_regime_especial', 1)->nullable();
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_optante_simples')) {
                $table->string('nfse_optante_simples', 1)->default('2'); // 1=Sim, 2=Não
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_incentivador_cultural')) {
                $table->string('nfse_incentivador_cultural', 1)->default('2'); // 1=Sim, 2=Não
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_aliquota_iss')) {
                $table->decimal('nfse_aliquota_iss', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_item_lista_servico')) {
                $table->string('nfse_item_lista_servico', 10)->nullable(); // Item padrão da lista
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'nfse_codigo_cnae')) {
                $table->string('nfse_codigo_cnae', 10)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            $columns = [
                'inscricao_municipal',
                'codigo_municipio',
                'nfse_serie_rps',
                'nfse_ultimo_numero_rps',
                'nfse_provedor',
                'nfse_versao',
                'nfse_usuario',
                'nfse_senha',
                'nfse_regime_especial',
                'nfse_optante_simples',
                'nfse_incentivador_cultural',
                'nfse_aliquota_iss',
                'nfse_item_lista_servico',
                'nfse_codigo_cnae',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('fiscalbr_configs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

