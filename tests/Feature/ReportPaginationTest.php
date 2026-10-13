<?php

namespace Tests\Feature;

use App\Models\ItemReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_use_bootstrap_pagination_and_preserve_filters_across_pages(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);
        ItemReport::factory()->count(21)->lost()->for($admin, 'reporter')->create();
        ItemReport::factory()->found()->for($admin, 'reporter')->create();

        $response = $this->actingAs($admin)->get(route('reports.index', ['type' => 'lost']));
        $response->assertOk()
            ->assertSee('class="pagination"', false)
            ->assertSee('class="page-link"', false)
            ->assertDontSee('<svg', false)
            ->assertViewHas('reports', fn ($reports) => $reports->count() === 20 && $reports->total() === 21);

        $nextUrl = $response->viewData('reports')->nextPageUrl();
        $this->assertStringContainsString('type=lost', $nextUrl);
        $this->get($nextUrl)->assertOk()
            ->assertSee('class="pagination"', false)
            ->assertViewHas('reports', fn ($reports) => $reports->currentPage() === 2 && $reports->count() === 1);
    }
}
