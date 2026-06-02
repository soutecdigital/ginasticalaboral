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
            $table->string('matricula')->unique(); 
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('perfil')->default('aluno'); // admin, professor, aluno

            // --- Quebrando o Loop (Poka-Yoke para Postgres) ---
            // Criamos a coluna apenas como dado numérico primeiro para não travar a inicialização.
            // O vínculo muitos-para-muitos já será controlado com total segurança pela tabela pivô 'empresa_user' mais adiante!
            $table->unsignedBigInteger('empresa_id')->nullable();

            // --- Auditoria e Dados Gerais ---
            $table->unsignedBigInteger('user_creator')->nullable();
            $table->timestamp('data_creator')->useCurrent();
            
            // Correção Poka-Yoke: CPF precisa ser string para preservar os zeros à esquerda (ex: 012.345...)
            $table->string('cpf', 11)->nullable(); 
            
            $table->rememberToken();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Tabelas auxiliares obrigatórias do Laravel
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
