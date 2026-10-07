<?php

namespace Database\Factories;

use App\Models\ItemReport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemReport>
 */
class ItemReportFactory extends Factory
{
    protected $model = ItemReport::class;

    public function definition(): array
    {
        $name = rtrim(Str::title(fake()->words(2, true)), '.');

        return [
            'report_type' => 'lost',
            'reporter_user_id' => \App\Models\User::factory(),
            'owner_user_id' => null,
            'category_id' => null,
            'item_name' => $name,
            'item_description' => fake()->sentence(12),
            'brand_model' => fake()->word(),
            'color' => fake()->randomElement(['Black', 'Blue', 'Brown', 'Gray', 'Green']),
            'incident_date' => now()->subDays(3)->toDateString(),
            'incident_time' => '09:30',
            'location_id' => null,
            'circumstances' => fake()->sentence(8),
            'contact_override' => null,
            'status' => 'pending',
            'matched_report_id' => null,
            'matched_score' => null,
        ];
    }

    public function lost(): static
    {
        return $this->state(fn () => ['report_type' => 'lost']);
    }

    public function found(): static
    {
        return $this->state(fn () => ['report_type' => 'found']);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    /** Marks the report as auto-matched to $matched with a symmetric back-pointer. */
    public function matchedTo(ItemReport $matched, float $score = 90.0): static
    {
        return $this->state(fn () => [
            'matched_report_id' => $matched->id,
            'matched_score' => $score,
            'status' => 'matched',
        ]);
    }
}
