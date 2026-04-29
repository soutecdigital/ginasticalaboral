<?php
// database/migrations/2026_04_10_create_presenca_auditoria_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presenca_auditoria', function (Blueprint $table) {
           $table->bigIncrements('id');
            // ID da presença que foi alterada
            $table->foreignId('presenca_id')->constrained('presencas')->onDelete('cascade');
            
            // ID do professor que realizou a mudança
            $table->foreignId('professor_id')->constrained('users');
            
            $table->string('status_anterior');
            $table->string('status_novo');
            $table->text('motivo'); // O campo obrigatório do seu Modal
            
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presenca_auditoria');
    }
};