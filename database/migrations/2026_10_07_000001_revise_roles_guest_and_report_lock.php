<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLES = [
        'admin'   => 'System Administrator',
        'osa'     => 'Office of Student Affairs',
        'student' => 'Student User',
        'faculty' => 'Faculty User',
        'staff'   => 'Staff User',
        'guest'   => 'Guest User',
    ];

    public function up(): void
    {
        $this->repairRolesTable();
        $this->renameVisitorToGuest();
        $this->addReportLockColumns();
    }

    public function down(): void
    {
        try {
            if (Schema::hasColumn('item_reports', 'locked_at')) {
                Schema::table('item_reports', fn ($t) => $t->dropColumn(['locked_at', 'locked_by_user_id']));
            }
        } catch (\Throwable $e) {
            // columns may not exist on some drivers
        }

        try {
            DB::table('user_profiles')->where('user_type', 'guest')->update(['user_type' => 'visitor']);
            if ($this->driverIsMysql()) {
                DB::statement("ALTER TABLE user_profiles MODIFY COLUMN user_type ENUM('admin','student','faculty','staff','visitor') NOT NULL");
            }
        } catch (\Throwable $e) {}
    }

    /* ------------------------------------------------------------------
     * roles.id in the live database was a plain BIGINT with no primary
     * key and no AUTO_INCREMENT, so Role::firstOrCreate() threw
     * "Field 'id' doesn't have a default value" and rolled back the whole
     * registration transaction (this is why Guest accounts could not be
     * created / logged in).
     * ------------------------------------------------------------------ */
    private function repairRolesTable(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        try {
            $column = DB::selectOne(
                "SELECT COLUMN_KEY, EXTRA FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'roles' AND COLUMN_NAME = 'id'"
            );
        } catch (\Throwable $e) {
            $column = null;
        }

        if ($column) {
            if (stripos((string) $column->COLUMN_KEY, 'PRI') === false) {
                try {
                    DB::statement('ALTER TABLE roles ADD PRIMARY KEY (id)');
                } catch (\Throwable $e) {
                    // duplicate ids or already a key — nothing else we can do safely
                }
            }

            if (stripos((string) $column->EXTRA, 'auto_increment') === false) {
                try {
                    DB::statement('ALTER TABLE roles MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
                } catch (\Throwable $e) {
                    // non-MySQL driver / insufficient privileges
                }
            }
        }

        // Guarantee every role the application expects exists.
        foreach (self::ROLES as $name => $description) {
            $exists = DB::table('roles')->where('name', $name)->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name'        => $name,
                    'description' => $description,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    private function renameVisitorToGuest(): void
    {
        $visitor = DB::table('roles')->where('name', 'visitor')->first();
        $guest   = DB::table('roles')->where('name', 'guest')->first();

        if ($visitor && !$guest) {
            DB::table('roles')->where('id', $visitor->id)->update(['name' => 'guest']);
        } elseif ($visitor && $guest) {
            DB::table('user_roles')
                ->where('role_id', $visitor->id)
                ->whereNotIn('user_id', function ($q) use ($guest) {
                    $q->select('user_id')->from('user_roles')->where('role_id', $guest->id);
                })
                ->update(['role_id' => $guest->id]);

            DB::table('user_roles')->where('role_id', $visitor->id)->delete();
            DB::table('roles')->where('id', $visitor->id)->delete();
        }

        // Profile type: Visitor -> Guest
        if (Schema::hasTable('user_profiles')) {
            try {
                DB::table('user_profiles')->where('user_type', 'visitor')->update(['user_type' => 'guest']);
            } catch (\Throwable $e) {}

            if ($this->driverIsMysql()) {
                try {
                    DB::statement("ALTER TABLE user_profiles MODIFY COLUMN user_type ENUM('admin','student','faculty','staff','guest') NOT NULL");
                } catch (\Throwable $e) {}
            }
        }
    }

    private function addReportLockColumns(): void
    {
        if (!Schema::hasTable('item_reports')) {
            return;
        }

        if (!Schema::hasColumn('item_reports', 'locked_at')) {
            Schema::table('item_reports', fn ($t) => $t->timestamp('locked_at')->nullable()->after('status'));
        }

        if (!Schema::hasColumn('item_reports', 'locked_by_user_id')) {
            Schema::table('item_reports', fn ($t) => $t->unsignedBigInteger('locked_by_user_id')->nullable()->after('locked_at'));
        }
    }

    private function driverIsMysql(): bool
    {
        try {
            return DB::getDriverName() === 'mysql';
        } catch (\Throwable $e) {
            return false;
        }
    }
};
