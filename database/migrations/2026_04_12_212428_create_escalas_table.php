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
            // POKA-YOKE: O "Dono" da aula escolhido pelo Sócio
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->date('data');
            $table->date('data_cancelamento')->nullable(); // Para registrar quando a aula foi cancelada, se for o caso
            $table->foreignId('user_cancelamento_id')->nullable()->constrained('users')->onDelete('set null'); // Quem cancelou a aula, se for o caso
            $table->text('observacao_cancelamento')->nullable();
            $table->enum('turno', ['manha', 'tarde', 'noite'])->default('manha');
            $table->enum('status', ['ativo', 'cancelado'])->default('ativo');
            $table->text('observacao')->nullable();
            $table->timestamp('checkin')->nullable();
            $table->string('status_presenca')->default('pendente');
            $table->text('solicitou_ajuste')->nullable(); // TEXT não pode ter default
            $table->string('lat_prof', 50)->nullable();
            $table->string('lng_prof', 50)->nullable();
            // Para geo_valid, o ideal é boolean (TINYINT 0 ou 1)
            $table->boolean('geo_valid')->default(0);



            $table->timestamps();
            // Trava de segurança: Não permite escalar o mesmo prof na mesma empresa/data duas vezes
            $table->unique(['user_id', 'empresa_id', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropColumn(['lat_prof', 'lng_prof', 'geo_valid']);
        });
    }
};
