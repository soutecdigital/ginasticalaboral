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
        Schema::create('empresa_user_presenca', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Aluno
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade');
            
            // Status de Presença ('0' = ausente, '1' = presente)
            $table->enum('presenca', ['0', '1'])->default('0');
            
            // Controle
            $table->boolean('ativo')->default(true);
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // ✅ UNIQUE: Garante que não há duplicatas
            $table->unique(['empresa_id', 'user_id', 'professor_id'], 'unique_empresa_aluno_prof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_user_presenca');
    }
};
