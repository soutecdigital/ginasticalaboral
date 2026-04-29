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
    Schema::table('ouvidorias', function (Blueprint $table) {
        $table->text('resposta_coordenacao')->nullable()->after('mensagem');
        $table->datetime('respondido_em')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ouvidorias', function (Blueprint $table) {
            //
        });
    }
};
