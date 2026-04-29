<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturamentos', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a Empresa
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            // VALORES (Snapshot para Auditoria)
            // Guardamos o valor aqui para que, se o preço da empresa mudar, 
            // a fatura emitida não mude sozinha.
            $table->decimal('valor_mensalidade', 10, 2); 
            $table->decimal('valor_avulso', 10, 2)->default(0); 
            
            // CONTROLE DE TEMPO
            // Usamos o primeiro dia do mês (Ex: 2026-04-01) para representar o mês todo
            $table->date('mes_referencia'); 
            
            // FINANCEIRO
            $table->date('data_pagamento')->nullable(); // Preenchido no ato da baixa
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente');
            
            // AUDITORIA
            $table->unsignedBigInteger('user_baixa_id')->nullable(); // Quem recebeu o dinheiro
            $table->text('observacao_financeira')->nullable(); // Motivo de valores avulsos
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturamentos');
    }
};