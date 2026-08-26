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
        // This migration is intentionally left as a no-op.
        // The sessions table is already created by the base migration
        // 0001_01_01_000000_create_users_table.php with the correct schema.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
