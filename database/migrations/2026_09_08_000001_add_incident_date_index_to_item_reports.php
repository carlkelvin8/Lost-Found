<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_reports', function (Blueprint $table) {
            // Add index on incident_date for calendar range queries if not exists
            // Check via Doctrine not available, use try/catch with raw check
            $indexName = 'item_reports_incident_date_index';
            $hasIndex = false;
            try {
                $indexes = Schema::getIndexes('item_reports');
                foreach ($indexes as $idx) {
                    if (($idx['name'] ?? '') === $indexName) {
                        $hasIndex = true;
                        break;
                    }
                    // Also check columns
                    $cols = $idx['columns'] ?? [];
                    if (count($cols) === 1 && $cols[0] === 'incident_date') {
                        $hasIndex = true;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback: attempt to create and ignore duplicate error
            }

            if (!$hasIndex) {
                try {
                    $table->index('incident_date', $indexName);
                } catch (\Throwable $e) {
                    // Index may already exist or DB driver doesn't support check — ignore
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_reports', function (Blueprint $table) {
            try {
                $table->dropIndex('item_reports_incident_date_index');
            } catch (\Throwable $e) {
                // ignore if not exists
            }
        });
    }
};
