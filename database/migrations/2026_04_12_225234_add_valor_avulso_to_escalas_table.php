<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Adição de Valor Avulso (Coberturas/Emergências)
 * Objetivo: Permitir flexibilidade financeira em escalas que fogem ao contrato padrão.
 * LEGENDA: Esta coluna permite que o Sócio defina um valor específico para UMA aula, 
 * ignorando o valor fixo cadastrado no perfil do professor para este caso isolado.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * LEGENDA: Adiciona o campo de valor monetário após o turno para facilitar a leitura no DB.
     */
    public function up(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            
            /**
             * CAMPO: valor_venda_avulso
             * REGRA DE NEGÓCIO (POKA-YOKE): 
             * IF (valor_venda_avulso > 0) -> USE valor_venda_avulso para o pagamento;
             * ELSE -> USE professor_configuracoes.valor_aula (Valor fixo).
             */
            $table->decimal('valor_venda_avulso', 10, 2)
                  ->nullable()
                  ->after('turno')
                  ->comment('Valor de exceção para coberturas ou aulas avulsas');
        });
    }

    /**
     * Reverse the migrations.
     * LEGENDA: Remove a coluna caso seja necessário fazer um rollback do sistema.
     */
    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropColumn('valor_venda_avulso');
        });
    }
};