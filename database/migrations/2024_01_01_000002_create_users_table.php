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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // --- Identificação do Usuário ---
            $table->string('matricula')->unique(); // Seu Poka-Yoke para ID de funcionário
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('perfil')->default('aluno'); // admin, professor, aluno

            // --- A "Âncora" (Chave Estrangeira) ---
            // Vincula o usuário à tabela empresas (000001)
            // onDelete('cascade'): Se a empresa for deletada, os usuários somem também
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->onDelete('cascade');

            // --- Auditoria (Quem criou o registro) ---
            $table->integer('user_creator')->nullable();
            $table->timestamp('data_creator')->useCurrent();
            $table->decimal('cpf', 14, 2)->nullable(); // CPF do usuário, formato numérico para evitar erros de formatação
            $table->rememberToken();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Tabelas auxiliares do Laravel (Obrigatórias para o sistema funcionar)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
