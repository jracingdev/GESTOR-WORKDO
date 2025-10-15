<?php
/**
 * Script de migração para adicionar campos de cliente à tabela users
 * Execute este arquivo acessando: https://workdo.jracing.dev.br/migrate-users-table.php
 * IMPORTANTE: Delete este arquivo após a execução!
 */

// Carregar o autoloader do Laravel
require __DIR__.'/../vendor/autoload.php';

// Carregar a aplicação Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Inicializar o kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "<h1>Migração da Tabela Users</h1>";
echo "<p>Adicionando campos de cliente...</p>";

try {
    // Verificar conexão com o banco
    DB::connection()->getPdo();
    echo "<p style='color: green;'>✓ Conexão com banco de dados estabelecida</p>";
    
    $columnsAdded = [];
    $columnsExisting = [];
    
    // Lista de colunas para adicionar
    $columns = [
        'cnpj' => function($table) {
            $table->string('cnpj', 18)->nullable()->after('email');
        },
        'inscricao_estadual' => function($table) {
            $table->string('inscricao_estadual', 20)->nullable()->after('cnpj');
        },
        'celular' => function($table) {
            $table->string('celular', 20)->nullable()->after('inscricao_estadual');
        },
        'informacoes_credito' => function($table) {
            $table->text('informacoes_credito')->nullable()->after('celular');
        },
        'foto_cliente' => function($table) {
            $table->string('foto_cliente')->nullable()->after('informacoes_credito');
        },
        'caminho_documentos' => function($table) {
            $table->json('caminho_documentos')->nullable()->after('foto_cliente');
        },
        'endereco_completo' => function($table) {
            $table->string('endereco_completo')->nullable()->after('caminho_documentos');
        },
        'cep' => function($table) {
            $table->string('cep', 10)->nullable()->after('endereco_completo');
        },
        'latitude' => function($table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('cep');
        },
        'longitude' => function($table) {
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        }
    ];
    
    // Adicionar cada coluna se não existir
    foreach ($columns as $columnName => $columnDefinition) {
        if (!Schema::hasColumn('users', $columnName)) {
            Schema::table('users', function (Blueprint $table) use ($columnDefinition) {
                $columnDefinition($table);
            });
            $columnsAdded[] = $columnName;
            echo "<p style='color: green;'>✓ Coluna '$columnName' adicionada com sucesso</p>";
        } else {
            $columnsExisting[] = $columnName;
            echo "<p style='color: orange;'>⚠ Coluna '$columnName' já existe</p>";
        }
    }
    
    // Registrar a migration como executada
    $migrationName = '2025_10_14_184452_add_client_details_to_users_table';
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
    
    if (!$exists) {
        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $maxBatch + 1
        ]);
        echo "<p style='color: green;'>✓ Migration registrada no banco de dados</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Migration já estava registrada</p>";
    }
    
    echo "<hr>";
    echo "<h2>Resumo:</h2>";
    echo "<p><strong>Colunas adicionadas:</strong> " . count($columnsAdded) . "</p>";
    if (count($columnsAdded) > 0) {
        echo "<ul>";
        foreach ($columnsAdded as $col) {
            echo "<li>$col</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><strong>Colunas já existentes:</strong> " . count($columnsExisting) . "</p>";
    if (count($columnsExisting) > 0) {
        echo "<ul>";
        foreach ($columnsExisting as $col) {
            echo "<li>$col</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<p style='color: red; font-weight: bold;'>IMPORTANTE: Delete este arquivo agora!</p>";
    echo "<p>Comando: <code>rm " . __FILE__ . "</code></p>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

