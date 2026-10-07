<?php

namespace Database\Factories;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Claim>
 */
class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'report_id' => \App\Models\ItemReport::factory()->found(),
            'claimant_user_id' => \App\Models\User::factory(),
            'proof_text' => fake()->paragraph(),
            'status' => 'pending',
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
