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
        Schema::table('professor_pagamentos', function (Blueprint $table) {
            // Adiciona coluna para rastrear qual liquidação pagou este item
            $table->foreignId('liquidacao_id')
                ->nullable()
                ->constrained('professor_liquidacoes')
                ->onDelete('set null')
                ->after('status_pagamento');
                
            // Índice para buscas rápidas
            $table->index('liquidacao_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professor_pagamentos', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['liquidacao_id']);
            $table->dropIndex(['liquidacao_id']);
            $table->dropColumn('liquidacao_id');
        });
    }
};
