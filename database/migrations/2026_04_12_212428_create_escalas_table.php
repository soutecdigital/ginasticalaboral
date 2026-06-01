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
        Schema::create('escalas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->date('data');
            $table->date('data_cancelamento')->nullable(); 
            $table->foreignId('user_cancelamento_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->text('observacao_cancelamento')->nullable();
            $table->enum('turno', ['manha', 'tarde', 'noite'])->default('manha');
            $table->enum('status', ['ativo', 'cancelado'])->default('ativo');
            $table->text('observacao')->nullable();
            $table->timestamp('checkin')->nullable();
            $table->string('status_presenca')->default('pendente');
            $table->text('solicitou_ajuste')->nullable(); 
            
            // Removemos daqui: lat_prof, lng_prof, geo_valid e a constraint unique 
            // (elas vão para o arquivo novo para não quebrar o banco)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalas');
    }
};
