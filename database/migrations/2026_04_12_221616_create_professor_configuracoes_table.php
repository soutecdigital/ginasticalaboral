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
        Schema::create('professor_configuracoes', function (Blueprint $table) {
            $table->id();

            // POKA-YOKE: Relacionamento com a tabela de usuários (Professores)
            // Se o usuário for deletado, as configurações dele também somem (cascade)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Valor da aula com precisão decimal (10 dígitos no total, 2 após a vírgula)
            $table->decimal('valor_aula', 10, 2)->default(0.00);
            $table->decimal('valor_aula_online', 10, 2)->default(0.00);
            $table->decimal('valor_aula_avulso', 10, 2)->default(0.00);

            // LEGENDA: Data em que o novo valor passa a valer (Permite agendar aumentos)
            $table->date('data_inicio_vigencia');

            // Campo para o Sócio anotar o motivo do reajuste (Ex: Dissídio 2026)
            $table->text('observacao')->nullable();

            // created_at e updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_configuracoes');
    }
};
