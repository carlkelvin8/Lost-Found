<?php

namespace Database\Factories;

use App\Models\ReportMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportMatch>
 */
class ReportMatchFactory extends Factory
{
    protected $model = ReportMatch::class;

    public function definition(): array
    {
        return [
            'lost_report_id' => \App\Models\ItemReport::factory()->lost(),
            'found_report_id' => \App\Models\ItemReport::factory()->found(),
            'score' => 90.0,
            'method' => 'keyword',
            'status' => 'suggested',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed']);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }
}
