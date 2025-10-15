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
        if (!Schema::hasTable('users')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            // Verificar se as colunas já existem antes de adicionar
            if (!Schema::hasColumn('users', 'cnpj')) {
                $table->string('cnpj', 18)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'inscricao_estadual')) {
                $table->string('inscricao_estadual', 20)->nullable()->after('cnpj');
            }
            if (!Schema::hasColumn('users', 'celular')) {
                $table->string('celular', 20)->nullable()->after('inscricao_estadual');
            }
            if (!Schema::hasColumn('users', 'informacoes_credito')) {
                $table->text('informacoes_credito')->nullable()->after('celular');
            }
            if (!Schema::hasColumn('users', 'foto_cliente')) {
                $table->string('foto_cliente')->nullable()->after('informacoes_credito');
            }
            if (!Schema::hasColumn('users', 'caminho_documentos')) {
                $table->json('caminho_documentos')->nullable()->after('foto_cliente');
            }
            if (!Schema::hasColumn('users', 'endereco_completo')) {
                $table->string('endereco_completo')->nullable()->after('caminho_documentos');
            }
            if (!Schema::hasColumn('users', 'cep')) {
                $table->string('cep', 10)->nullable()->after('endereco_completo');
            }
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('cep');
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'cnpj',
                'inscricao_estadual',
                'celular',
                'informacoes_credito',
                'foto_cliente',
                'caminho_documentos',
                'endereco_completo',
                'cep',
                'latitude',
                'longitude'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

