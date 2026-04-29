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
        // Alter ENUM column to accept string values '0' and '1'
        DB::statement("ALTER TABLE empresa_user_presenca MODIFY presenca ENUM('0', '1') DEFAULT '0'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE empresa_user_presenca MODIFY presenca ENUM(0, 1) DEFAULT 0");
    }
};
