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
        Schema::create('fiscalbr_nfe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->index();
            $table->enum('tipo', ['nfe', 'nfce'])->default('nfe');
            $table->string('chave_acesso', 44)->unique()->nullable();
            $table->string('numero', 9);
            $table->string('serie', 3);
            $table->enum('modelo', ['55', '65'])->default('55'); // 55=NF-e, 65=NFC-e
            $table->date('data_emissao');
            $table->time('hora_emissao');
            
            // Destinatário
            $table->string('destinatario_cpf_cnpj', 18)->nullable();
            $table->string('destinatario_nome')->nullable();
            $table->string('destinatario_ie', 20)->nullable();
            $table->string('destinatario_endereco')->nullable();
            $table->string('destinatario_cidade')->nullable();
            $table->string('destinatario_uf', 2)->nullable();
            $table->string('destinatario_cep', 9)->nullable();
            
            // Valores
            $table->decimal('valor_produtos', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            
            // Status e controle
            $table->enum('status', ['rascunho', 'processando', 'autorizada', 'rejeitada', 'cancelada', 'denegada'])->default('rascunho');
            $table->string('protocolo', 20)->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamp('data_autorizacao')->nullable();
            $table->timestamp('data_cancelamento')->nullable();
            
            // XMLs
            $table->longText('xml_enviado')->nullable();
            $table->longText('xml_autorizado')->nullable();
            $table->longText('xml_cancelamento')->nullable();
            
            // Relacionamentos
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('pos_sale_id')->nullable()->index();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'data_emissao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_nfe');
    }
};

