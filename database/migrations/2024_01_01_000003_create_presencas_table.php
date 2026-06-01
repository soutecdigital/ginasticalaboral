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
        Schema::create('presencas', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // --- Chaves Estrangeiras (Relacionamentos) ---
            
            // O Aluno que está recebendo a presença
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // O Professor que está aplicando a aula (Auth::user()->id)
            $table->foreignId('professor_id')
                  ->constrained('users');
            
            // A Empresa onde a aula está ocorrendo
            $table->foreignId('empresa_id')
                  ->constrained('empresas');

            // --- Dados do Evento (O que você precisava) ---
            $table->date('data_presenca'); // Registra apenas YYYY-MM-DD
            $table->time('hora_presenca')->nullable(); // Adicione o ->nullable()
            
            // Campo opcional para observações (Ex: Aluno com dor lombar)
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};
