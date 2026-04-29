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
        Schema::create('professor_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escala_id')->constrained('escalas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // ID do Professor
            $table->decimal('valor_pago', 10, 2);
            $table->date('data_referencia');
            $table->enum('status_pagamento', ['pendente', 'pago', 'cancelado'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_pagamentos');
    }
};
