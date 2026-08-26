<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FULLTEXT indexes are MySQL-specific; skip on SQLite
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE item_reports ADD FULLTEXT ft_item_match (item_name, item_description, brand_model, color, circumstances)');
    }

    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE item_reports DROP INDEX ft_item_match');
    }
};
