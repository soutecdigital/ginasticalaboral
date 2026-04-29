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
        Schema::create('professor_liquidacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade'); // Professor
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade'); // Empresa
            $table->string('numero_nf')->nullable(); // Número da Nota Fiscal
            $table->date('mes_referencia'); // Mês de referência do pagamento
            $table->decimal('valor_total_pago', 10, 2); // Valor total pago
            $table->date('data_pagamento'); // Data que foi pago
            $table->enum('forma_pagamento', ['dinheiro', 'banco', 'cheque', 'pix'])->default('banco');
            $table->text('observacao')->nullable();
            
            // Índices para relatórios
            $table->index('professor_id');
            $table->index('empresa_id');
            $table->index(['professor_id', 'empresa_id']);
            $table->index('mes_referencia');
            $table->index('data_pagamento');
            $table->unique(['professor_id', 'empresa_id', 'mes_referencia'], 'prof_emp_mes_unique'); // Nome customizado mais curto
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_liquidacoes');
    }
};
