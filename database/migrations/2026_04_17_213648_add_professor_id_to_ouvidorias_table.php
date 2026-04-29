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
        Schema::table('ouvidorias', function (Blueprint $table) {
            // Adiciona foreign key para professor_id
            $table->foreignId('professor_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ouvidorias', function (Blueprint $table) {
            $table->dropForeignIdFor('professor_id');
            $table->dropColumn('professor_id');
        });
    }
};
