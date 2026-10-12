<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SYSTEM_ROLES = ['admin', 'student', 'faculty', 'staff', 'guest'];

    private const ROLE_DESCRIPTIONS = [
        'admin'   => 'System Administrator',
        'student' => 'Student User',
        'faculty' => 'Faculty User',
        'staff'   => 'Staff User',
        'guest'   => 'Guest User',
    ];

    /**
     * Aligns a pre-existing (live) database with the schemas and role design
     * the application expects:
     *  - categories needs description + icon columns (were only created on fresh DBs)
     *  - locations needs building + floor columns
     *  - the OSA role is folded into Admin (admin/OSA are one account type)
     *  - roles outside the canonical five that no longer serve anyone are removed
     *  - legacy department_id values that stored a code ('ICS') become row ids
     */
    public function up(): void
    {
        $this->addCategoriesColumns();
        $this->addLocationsColumns();
        $this->foldOsaIntoAdmin();
        $this->removeOrphanRoles();
        $this->refreshRoleDescriptions();
        $this->normalizeDepartmentIds();
    }

    public function down(): void
    {
        // Alignment is one-way; nothing to roll back safely.
    }

    private function addCategoriesColumns(): void
    {
        if (!Schema::hasTable('categories')) return;

        if (!Schema::hasColumn('categories', 'description')) {
            Schema::table('categories', fn (Blueprint $t) => $t->text('description')->nullable()->after('name'));
        }
        if (!Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', fn (Blueprint $t) => $t->string('icon', 50)->nullable()->after('description'));
        }
    }

    private function addLocationsColumns(): void
    {
        if (!Schema::hasTable('locations')) return;

        if (!Schema::hasColumn('locations', 'building')) {
            Schema::table('locations', fn (Blueprint $t) => $t->string('building', 100)->nullable()->after('name'));
        }
        if (!Schema::hasColumn('locations', 'floor')) {
            Schema::table('locations', fn (Blueprint $t) => $t->string('floor', 20)->nullable()->after('building'));
        }
    }

    private function foldOsaIntoAdmin(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('user_roles')) return;

        $osa   = DB::table('roles')->where('name', 'osa')->first();
        $admin = DB::table('roles')->where('name', 'admin')->first();

        if (!$osa || !$admin) return;

        $osaUserIds    = DB::table('user_roles')->where('role_id', $osa->id)->pluck('user_id')->all();
        $alreadyAdmins = DB::table('user_roles')->where('role_id', $admin->id)->pluck('user_id')->all();

        foreach (array_diff($osaUserIds, $alreadyAdmins) as $userId) {
            DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $admin->id]);
        }

        DB::table('user_roles')->where('role_id', $osa->id)->delete();
        DB::table('roles')->where('id', $osa->id)->delete();
    }

    private function removeOrphanRoles(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('user_roles')) return;

        $custom = DB::table('roles')->whereNotIn('name', self::SYSTEM_ROLES)->get();

        foreach ($custom as $role) {
            $attached = DB::table('user_roles')->where('role_id', $role->id)->count();
            if ((int) $attached === 0) {
                DB::table('roles')->where('id', $role->id)->delete();
            }
        }
    }

    private function refreshRoleDescriptions(): void
    {
        if (!Schema::hasTable('roles')) return;

        foreach (self::ROLE_DESCRIPTIONS as $name => $description) {
            DB::table('roles')->where('name', $name)->update(['description' => $description]);
        }
    }

    private function normalizeDepartmentIds(): void
    {
        if (!Schema::hasTable('user_profiles') || !Schema::hasTable('departments')) return;

        // Old rows stored the department CODE (e.g. 'ICS') inside the
        // varchar department_id column. Convert code -> row id so
        // ->department() lookups resolve.
        foreach (DB::table('departments')->get(['id', 'name']) as $dpt) {
            DB::table('user_profiles')
                ->where('department_id', $dpt->name)
                ->update(['department_id' => (string) $dpt->id]);
        }
    }
};