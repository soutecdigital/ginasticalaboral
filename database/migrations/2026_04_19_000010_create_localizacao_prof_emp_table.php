<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para auditoria de localização do professor durante confirmaçao de presença.
     * Rastreia: onde o prof está quando confirma, distância da empresa, tipo de confirmação (GPS/Horário).
     */
    public function up(): void
    {
        Schema::create('localizacao_prof_emp', function (Blueprint $table) {
            $table->id();

            // --- Relacionamentos ---
            $table->foreignId('escala_id')->constrained('escalas')->onDelete('cascade'); // A escala/aula
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade'); // O professor
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade'); // A empresa

            // --- Dados da Empresa (snapshot) ---
            $table->decimal('empresa_lat', 10, 8)->nullable(); // Latitude da empresa
            $table->decimal('empresa_lng', 11, 8)->nullable(); // Longitude da empresa
            $table->decimal('empresa_raio_metros', 10, 2)->nullable(); // Raio de tolerância em metros

            // --- Dados do Professor (GPS capturado) ---
            $table->decimal('prof_lat', 10, 8)->nullable(); // Latitude do professor no momento da confirmação
            $table->decimal('prof_lng', 11, 8)->nullable(); // Longitude do professor no momento da confirmação
            $table->decimal('distancia_metros', 10, 2)->nullable(); // Distância calculada em metros
            $table->boolean('dentro_raio')->default(false); // true = dentro do raio, false = fora

            // --- Tipo de Confirmação ---
            $table->enum('tipo_confirmacao', ['gps', 'horario'])->default('horario'); // Como foi confirmado
            $table->text('motivo_gps_fraco')->nullable(); // Se usar horário, por que não usou GPS

            // --- Auditoria ---
            $table->timestamp('confirmado_em'); // Quando foi confirmado
            $table->string('user_agent')->nullable(); // Navegador/Device
            $table->ipAddress('ip_address')->nullable(); // IP da confirmação
            $table->text('observacao')->nullable(); // Obs adicionais

            $table->timestamps();

            // --- Índices ---
            $table->index(['escala_id', 'professor_id', 'empresa_id']);
            $table->index(['dentro_raio']); // Para auditorias rápidas
            $table->index('tipo_confirmacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localizacao_prof_emp');
    }
};
