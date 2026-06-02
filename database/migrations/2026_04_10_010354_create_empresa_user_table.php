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
        // 1. Criação da Tabela Pivô (Muitos para Muitos) - Poka-Yoke total para Postgres
        if (!Schema::hasTable('empresa_user')) {
            Schema::create('empresa_user', function (Blueprint $table) {
                $table->id(); // Sintaxe moderna que evita o erro 1364 de AUTO_INCREMENT
                
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');
                
                $table->foreignId('empresa_id')
                      ->constrained('empresas')
                      ->onDelete('cascade');

                $table->timestamps();
            });
        }

        /**
         * Poka-Yoke: Limpeza de Arquitetura.
         * Removemos o vínculo 1:N antigo da tabela users com segurança máxima.
         */
        if (Schema::hasColumn('users', 'empresa_id')) {
            Schema::table('users', function (Blueprint $table) {
                // No Postgres, apenas removemos a coluna direto. O banco limpa os índices locais sozinho.
                $table->dropColumn('empresa_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_user');

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable();
            }
        });
    }
};
