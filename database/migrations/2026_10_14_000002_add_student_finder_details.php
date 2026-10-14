<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_reports', function (Blueprint $table) {
            $table->boolean('surrendered_by_student')->default(false);
            $table->string('finder_name', 190)->nullable();
            $table->string('finder_student_id', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('item_reports', fn (Blueprint $table) => $table->dropColumn([
            'surrendered_by_student', 'finder_name', 'finder_student_id',
        ]));
    }
};
