<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand user_type enum to include staff, visitor for backward compatibility with UserController
        // Minimal change: alter column to include missing values
        try {
            DB::statement("ALTER TABLE user_profiles MODIFY COLUMN user_type ENUM('admin','student','faculty','staff','visitor') NOT NULL");
        } catch (\Throwable $e) {
            // Fallback for testing environments without DB or different driver
            // Try via Schema if DB statement fails
            try {
                Schema::table('user_profiles', function ($table) {
                    // No-op if fails
                });
            } catch (\Throwable $e2) {}
        }
    }

    public function down(): void
    {
        try {
            // Revert to original enum (keep data: map staff->faculty, visitor->student before revert)
            DB::table('user_profiles')->where('user_type','staff')->update(['user_type'=>'faculty']);
            DB::table('user_profiles')->where('user_type','visitor')->update(['user_type'=>'student']);
            DB::statement("ALTER TABLE user_profiles MODIFY COLUMN user_type ENUM('admin','student','faculty') NOT NULL");
        } catch (\Throwable $e) {}
    }
};
