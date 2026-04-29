<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importação necessária para o down original

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresa_user_presenca', function (Blueprint $table) {
            // Alterando para string para evitar conflitos de tipos
            $table->string('presenca')->default('0')->change();
        }); // O parêntese que faltava estava aqui
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_user_presenca', function (Blueprint $table) {
            // Revertendo usando o padrão do Laravel em vez de DB::statement
            $table->char('presenca', 1)->default('0')->change();
        });
    }
};