<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires FULLTEXT index for MATCH...AGAINST queries
        // Use raw SQL to add FULLTEXT index on the columns used by runAutoMatch()
        DB::statement('ALTER TABLE item_reports ADD FULLTEXT ft_item_match (item_name, item_description, brand_model, color, circumstances)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE item_reports DROP INDEX ft_item_match');
    }
};
