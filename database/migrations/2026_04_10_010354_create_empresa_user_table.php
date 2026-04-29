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
        // 1. Criação da Tabela Pivô (Muitos para Muitos)
        Schema::create('empresa_user', function (Blueprint $table) {
           $table->bigIncrements('id'); // Garante o AUTO_INCREMENT para evitar erro 1364
            
            // Relacionamento com Usuário (Alunos/Professores)
            // Especificamos a tabela 'users' para garantir a integridade
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relacionamento com Empresa
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');

            $table->timestamps();
        });

        /**
         * Poka-Yoke: Limpeza de Arquitetura.
         * Removemos o vínculo 1:N antigo da tabela users, 
         * pois agora um usuário pode pertencer a várias empresas.
         */
        if (Schema::hasColumn('users', 'empresa_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Tenta derrubar a chave estrangeira antes da coluna
                try {
                    $table->dropForeign(['empresa_id']);
                } catch (\Exception $e) {
                    // Se não houver chave estrangeira nomeada, apenas segue
                }
                $table->dropColumn('empresa_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recria a coluna na tabela users caso precise dar Rollback
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('set null');
            }
        });

        Schema::dropIfExists('empresa_user');
    }
};