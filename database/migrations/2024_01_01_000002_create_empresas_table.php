<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.l
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            // --- Relacionamentos / Chaves Estrangeiras ---
            // Usando foreignId para garantir a integridade com a tabela de usuários
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // --- Dados Cadastrais ---
            $table->string('nome_fantasia');
            $table->string('razao_social')->nullable();
            $table->string('cnpj', 18)->unique();

            // --- Contato ---
            $table->string('celular', 20)->nullable();
            $table->string('contato', 100)->nullable();
            $table->text('observacao')->nullable();

            // --- Localização e Endereço ---
            $table->string('logradouro')->nullable(); // <-- Adicionado
            $table->string('numero', 10)->nullable();
            $table->string('bairro')->nullable();     // <-- Adicionado
            $table->string('cidade')->default('Taubaté');
            $table->string('estado', 2)->default('SP');

            // --- Geolocalização (Poka-Yoke Check-in) ---
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->decimal('raio_gps_metros', 10, 2)->default(500.00); // <-- Adicionado

            // --- Cronograma de Aulas ---
            $table->boolean('seg')->default(false);
            $table->boolean('ter')->default(false);
            $table->boolean('qua')->default(false);
            $table->boolean('qui')->default(false);
            $table->boolean('sex')->default(false);
            $table->boolean('sab')->default(false);
            $table->boolean('dom')->default(false);

            // --- Gestão Contratual / Financeira ---
            $table->string('plano')->default('basic'); // <-- Adicionado
            $table->decimal('valor_contrato', 10, 2)->default(0.00); // <-- Adicionado
            $table->integer('dia_vencimento')->default(10); // <-- Adicionado

            // --- Personalização e Status ---
            $table->string('logo_path')->nullable();
            $table->boolean('ativo')->default(true);

            // --- Controle do Framework ---
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
