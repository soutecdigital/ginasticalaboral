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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            // --- Dados Cadastrais ---
            $table->string('nome_fantasia');
            $table->string('razao_social')->nullable();
            $table->string('cnpj', 18)->unique();
            // --- Contato ---
            $table->string('celular', 20)->nullable();
            $table->string('contato', 100)->nullable();
            $table->text('observacao')->nullable();
            // --- Localização ---
            $table->string('cidade')->default('Taubaté');
            $table->string('estado', 2)->default('SP');
            $table->string('numero', 10)->nullable();
            // --- Cronograma de Aulas ---
            $table->boolean('seg')->default(false);
            $table->boolean('ter')->default(false);
            $table->boolean('qua')->default(false);
            $table->boolean('qui')->default(false);
            $table->boolean('sex')->default(false);
            $table->boolean('sab')->default(false);
            $table->boolean('dom')->default(false);
            // --- Personalização e Status ---
            $table->string('logo_path')->nullable();
            $table->boolean('ativo')->default(true);
            $table->decimal('lat', 10, 8)->nullable(); // Latitude
            $table->decimal('lng', 11, 8)->nullable(); // Longitude
            $table->softDeletes(); 

            $table->timestamps();
           
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
