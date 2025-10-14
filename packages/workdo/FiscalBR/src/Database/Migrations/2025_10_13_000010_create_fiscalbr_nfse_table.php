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
        Schema::create('fiscalbr_nfse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('numero_rps', 20)->nullable(); // Número do RPS
            $table->string('serie_rps', 5)->default('1'); // Série do RPS
            $table->string('numero_nfse', 20)->nullable(); // Número da NFS-e (após emissão)
            $table->string('codigo_verificacao', 50)->nullable(); // Código de verificação
            $table->date('data_emissao');
            $table->enum('status', ['rascunho', 'rps_gerado', 'processando', 'autorizada', 'cancelada', 'erro'])->default('rascunho');
            
            // Tomador do Serviço
            $table->string('tomador_nome', 255);
            $table->string('tomador_cpf_cnpj', 18);
            $table->string('tomador_inscricao_municipal', 20)->nullable();
            $table->string('tomador_endereco', 255)->nullable();
            $table->string('tomador_numero', 20)->nullable();
            $table->string('tomador_complemento', 100)->nullable();
            $table->string('tomador_bairro', 100)->nullable();
            $table->string('tomador_cidade', 100)->nullable();
            $table->string('tomador_uf', 2)->nullable();
            $table->string('tomador_cep', 10)->nullable();
            $table->string('tomador_email', 255)->nullable();
            $table->string('tomador_telefone', 20)->nullable();
            
            // Serviço
            $table->text('descricao_servico');
            $table->string('codigo_servico', 20); // Código do serviço (LC 116/2003)
            $table->string('codigo_cnae', 10)->nullable();
            $table->string('item_lista_servico', 10); // Item da lista de serviços
            $table->string('codigo_tributacao_municipio', 20)->nullable();
            
            // Valores
            $table->decimal('valor_servicos', 15, 2);
            $table->decimal('valor_deducoes', 15, 2)->default(0);
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->decimal('valor_inss', 15, 2)->default(0);
            $table->decimal('valor_ir', 15, 2)->default(0);
            $table->decimal('valor_csll', 15, 2)->default(0);
            $table->decimal('valor_iss', 15, 2)->default(0);
            $table->decimal('valor_iss_retido', 15, 2)->default(0);
            $table->decimal('valor_outras_retencoes', 15, 2)->default(0);
            $table->decimal('base_calculo', 15, 2);
            $table->decimal('aliquota_iss', 5, 2);
            $table->decimal('valor_liquido', 15, 2);
            $table->decimal('desconto_incondicionado', 15, 2)->default(0);
            $table->decimal('desconto_condicionado', 15, 2)->default(0);
            
            // ISS
            $table->enum('iss_retido', ['sim', 'nao'])->default('nao');
            $table->enum('exigibilidade_iss', ['1', '2', '3', '4', '5', '6', '7'])->default('1');
            // 1=Exigível, 2=Não incidência, 3=Isenção, 4=Exportação, 5=Imunidade, 6=Exig.Susp.Dec, 7=Exig.Susp.Proc
            $table->string('municipio_prestacao', 7); // Código IBGE
            $table->string('municipio_incidencia', 7)->nullable(); // Código IBGE
            
            // Regime Especial de Tributação
            $table->string('regime_especial_tributacao', 1)->nullable(); // 1=Microempresa Municipal, 2=Estimativa, 3=Sociedade Profissionais, 4=Cooperativa, 5=MEI, 6=ME/EPP Simples Nacional
            $table->string('optante_simples_nacional', 1)->default('2'); // 1=Sim, 2=Não
            $table->string('incentivador_cultural', 1)->default('2'); // 1=Sim, 2=Não
            
            // Natureza da Operação
            $table->string('natureza_operacao', 1)->default('1'); // 1=Tributação no município, 2=Tributação fora do município, 3=Isenção, 4=Imune, 5=Exigibilidade suspensa, 6=Exportação
            
            // XML e PDF
            $table->text('xml')->nullable();
            $table->string('xml_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            
            // Prefeitura
            $table->string('prefeitura_provedor', 50)->nullable(); // Nome do provedor (ABRASF, GINFES, etc)
            $table->string('prefeitura_versao', 10)->nullable(); // Versão do webservice
            $table->text('prefeitura_resposta')->nullable(); // Resposta da prefeitura
            $table->string('protocolo', 50)->nullable(); // Protocolo de envio
            
            // Cancelamento
            $table->dateTime('data_cancelamento')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'data_emissao']);
            $table->index('numero_nfse');
            $table->index('tomador_cpf_cnpj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscalbr_nfse');
    }
};

