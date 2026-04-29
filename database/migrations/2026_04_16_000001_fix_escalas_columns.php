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
        Schema::table('escalas', function (Blueprint $table) {
            // Renomear 'status' para 'status_cancelamento' se a coluna existir
            if (Schema::hasColumn('escalas', 'status')) {
                $table->renameColumn('status', 'status_cancelamento');
            }
            
            // Adicionar coluna tipo_aula se não existir
            if (!Schema::hasColumn('escalas', 'tipo_aula')) {
                $table->string('tipo_aula')->default('normal')->after('turno');
            }
            
            // Adicionar coluna valor_venda_avulso se não existir
            if (!Schema::hasColumn('escalas', 'valor_venda_avulso')) {
                $table->decimal('valor_venda_avulso', 8, 2)->default(0)->after('status_presenca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            // Reverter renomeação
            if (Schema::hasColumn('escalas', 'status_cancelamento')) {
                $table->renameColumn('status_cancelamento', 'status');
            }
            
            // Remover colunas adicionadas
            if (Schema::hasColumn('escalas', 'tipo_aula')) {
                $table->dropColumn('tipo_aula');
            }
            
            if (Schema::hasColumn('escalas', 'valor_venda_avulso')) {
                $table->dropColumn('valor_venda_avulso');
            }
        });
    }
};
