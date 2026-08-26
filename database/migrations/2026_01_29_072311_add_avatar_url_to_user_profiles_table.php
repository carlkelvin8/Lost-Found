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
        // This migration is intentionally left empty.
        // The avatar_url column is already added by the prior migration
        // 2026_01_29_072254_add_avatar_url_to_user_profiles_table.php.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
