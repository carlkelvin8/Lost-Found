<?php

namespace Tests\Concerns;

trait ReportPayload
{
    protected function completeReportFields(): array
    {
        return ['item_name' => 'Wallet', 'color' => 'Blue',
            'category_id' => \App\Models\Category::firstOrCreate(['name' => 'Personal items'])->id,
            'location_detail' => 'Building A Room 301'];
    }
}
