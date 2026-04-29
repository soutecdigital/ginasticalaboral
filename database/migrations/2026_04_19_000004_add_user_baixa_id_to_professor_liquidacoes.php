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
        Schema::table('professor_liquidacoes', function (Blueprint $table) {
            $table->foreignId('user_baixa_id')
                ->nullable()
                ->after('forma_pagamento')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('user_baixa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professor_liquidacoes', function (Blueprint $table) {
            $table->dropForeign(['user_baixa_id']);
            $table->dropIndex(['user_baixa_id']);
            $table->dropColumn('user_baixa_id');
        });
    }
};