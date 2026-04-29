<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // POKA-YOKE: Limpa qualquer resquício de índice antigo para evitar o erro 1091
        // Tentamos os dois nomes possíveis que o Laravel/MySQL podem ter gerado
        try {
            DB::statement('ALTER TABLE escalas DROP INDEX escala_unica_index');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE escalas DROP INDEX escalas_user_id_empresa_id_data_turno_unique');
        } catch (\Exception $e) {}

        Schema::table('escalas', function (Blueprint $table) {
            /**
             * NOVA REGRA:
             * Permite múltiplos professores por empresa/turno.
             * Impede que o mesmo professor tenha choque de horário (2 empresas ao mesmo tempo).
             */
            $table->unique(['user_id', 'data', 'turno'], 'professor_agenda_unica');
        });
    }

    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropUnique('professor_agenda_unica');
        });
    }
};