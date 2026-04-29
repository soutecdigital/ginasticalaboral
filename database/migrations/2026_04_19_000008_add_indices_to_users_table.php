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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona índice único no CPF se não existir
            if (!Schema::hasIndex('users', 'users_cpf_unique')) {
                $table->unique('cpf', 'users_cpf_unique')
                    ->change();
            }
            
            // Adiciona índice no email para buscas rápidas
            if (!Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }
            
            // Adiciona índice na matrícula
            if (!Schema::hasIndex('users', 'users_matricula_index')) {
                $table->index('matricula', 'users_matricula_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_cpf_unique');
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_matricula_index');
        });
    }
};
