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
            // Remove a constraint unique de matricula se existir
            if (Schema::hasIndex('users', 'users_matricula_unique')) {
                $table->dropUnique('users_matricula_unique');
            }
            
            // Remove a coluna matricula e recria sem unique
            if (Schema::hasColumn('users', 'matricula')) {
                $table->dropColumn('matricula');
            }
            
            // Reinsere como nullable
            $table->string('matricula')->nullable()->index()->after('cpf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não recomenda-se fazer rollback, mas se necessário:
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'matricula')) {
                $table->dropColumn('matricula');
            }
            $table->string('matricula')->unique();
        });
    }
};
