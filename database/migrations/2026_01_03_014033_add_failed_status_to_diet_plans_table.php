<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE diet_plans MODIFY COLUMN status ENUM('generating', 'active', 'completed', 'archived', 'failed') DEFAULT 'generating'");
        }
        // SQLite: status is stored as TEXT, no modification needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE diet_plans MODIFY COLUMN status ENUM('generating', 'active', 'completed', 'archived') DEFAULT 'generating'");
        }
    }
};
