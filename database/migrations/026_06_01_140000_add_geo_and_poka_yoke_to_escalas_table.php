<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            // Adiciona as colunas novas com segurança se elas não existirem
            if (!Schema::hasColumn('escalas', 'lat_prof')) {
                $table->string('lat_prof', 50)->nullable();
                $table->string('lng_prof', 50)->nullable();
                $table->boolean('geo_valid')->default(false);
            }

            // Adiciona a trava de segurança Poka-Yoke sem recriar a tabela
            $table->unique(['user_id', 'data', 'turno'], 'professor_agenda_unica');
        });
    }

    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropUnique('professor_agenda_unica');
            $table->dropColumn(['lat_prof', 'lng_prof', 'geo_valid']);
        });
    }
};
