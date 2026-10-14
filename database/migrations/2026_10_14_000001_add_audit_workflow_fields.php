<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
        });
        Schema::table('item_reports', function (Blueprint $table) {
            $table->uuid('submission_id')->nullable();
            $table->string('reporter_name', 190)->nullable();
            $table->unique(['reporter_user_id', 'submission_id'], 'reports_submission_unique');
        });
    }

    public function down(): void
    {
        Schema::table('claims', fn (Blueprint $table) => $table->dropColumn('rejection_reason'));
        Schema::table('item_reports', function (Blueprint $table) {
            $table->dropUnique('reports_submission_unique');
            $table->dropColumn(['submission_id', 'reporter_name']);
        });
    }
};
