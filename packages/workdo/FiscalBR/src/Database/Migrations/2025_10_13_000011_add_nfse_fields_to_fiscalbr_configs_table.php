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
            $table->string('inscricao_municipal', 20)->nullable()->after('inscricao_estadual');
            $table->string('codigo_municipio', 7)->nullable()->after('inscricao_municipal'); // Código IBGE
            $table->string('nfse_serie_rps', 5)->default('1')->after('codigo_municipio');
            $table->integer('nfse_ultimo_numero_rps')->default(0)->after('nfse_serie_rps');
            $table->string('nfse_provedor', 50)->nullable()->after('nfse_ultimo_numero_rps'); // ABRASF, GINFES, etc
            $table->string('nfse_versao', 10)->nullable()->after('nfse_provedor'); // Versão do webservice
            $table->string('nfse_usuario', 100)->nullable()->after('nfse_versao'); // Usuário da prefeitura
            $table->text('nfse_senha')->nullable()->after('nfse_usuario'); // Senha (criptografada)
            $table->string('nfse_regime_especial', 1)->nullable()->after('nfse_senha');
            $table->string('nfse_optante_simples', 1)->default('2')->after('nfse_regime_especial'); // 1=Sim, 2=Não
            $table->string('nfse_incentivador_cultural', 1)->default('2')->after('nfse_optante_simples'); // 1=Sim, 2=Não
            $table->decimal('nfse_aliquota_iss', 5, 2)->default(0)->after('nfse_incentivador_cultural');
            $table->string('nfse_item_lista_servico', 10)->nullable()->after('nfse_aliquota_iss'); // Item padrão da lista
            $table->string('nfse_codigo_cnae', 10)->nullable()->after('nfse_item_lista_servico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};

