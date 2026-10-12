<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_DEPARTMENTS = ['ICS', 'ILAS', 'INET'];

    /**
     * Seed the departments the application ships with. On live databases the
     * table was never populated, so the registration form had no options to
     * choose from and account creation failed (department is required).
     */
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        // Check per-name instead of insertOrIgnore: live databases may predate
        // the unique index on `departments.name`, where insertOrIgnore would
        // happily insert duplicates.
        foreach (self::DEFAULT_DEPARTMENTS as $name) {
            $exists = DB::table('departments')->where('name', $name)->exists();

            if (!$exists) {
                DB::table('departments')->insert([
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        DB::table('departments')
            ->whereIn('name', self::DEFAULT_DEPARTMENTS)
            ->delete();
    }
};
