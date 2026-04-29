<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Poka-Yoke: Só cria se não existir, evitando erros de "Table already exists"
        if (!Schema::hasTable('historico_contratos')) {
            Schema::create('historico_contratos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
                $table->decimal('valor_anterior', 10, 2);
                $table->decimal('valor_novo', 10, 2);
                $table->string('motivo')->nullable();
                $table->integer('total_alunos_momento')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_contratos');
    }
};
