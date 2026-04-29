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
        Schema::table('historico_contratos', function (Blueprint $table) {
            // Adiciona user_id se não existir (quem fez a alteração)
            if (!Schema::hasColumn('historico_contratos', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->after('empresa_id')
                    ->comment('Usuário (Admin/Sócio) que fez a alteração do contrato');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historico_contratos', function (Blueprint $table) {
            $table->dropForeignIdFor('user_id');
            $table->dropColumn('user_id');
        });
    }
};
