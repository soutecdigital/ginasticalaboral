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
        Schema::table('empresas', function (Blueprint $table) {
            // Adiciona colunas de endereço se não existirem
            if (!Schema::hasColumn('empresas', 'logradouro')) {
                $table->string('logradouro')->nullable()->after('numero');
            }
            if (!Schema::hasColumn('empresas', 'bairro')) {
                $table->string('bairro')->nullable()->after('logradouro');
            }
            if (!Schema::hasColumn('empresas', 'plano')) {
                $table->string('plano')->default('basic')->after('ativo');
            }
            if (!Schema::hasColumn('empresas', 'valor_contrato')) {
                $table->decimal('valor_contrato', 10, 2)->default(0)->after('plano');
            }
            if (!Schema::hasColumn('empresas', 'dia_vencimento')) {
                $table->integer('dia_vencimento')->default(10)->after('valor_contrato');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumnIfExists('logradouro');
            $table->dropColumnIfExists('bairro');
            $table->dropColumnIfExists('plano');
            $table->dropColumnIfExists('valor_contrato');
            $table->dropColumnIfExists('dia_vencimento');
        });
    }
};
