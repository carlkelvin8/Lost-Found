<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_download_a_successful_claimants_report_with_status_history(): void
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);
        $report = ItemReport::factory()->found()->status('returned')->create();
        Claim::factory()->status('approved')->create([
            'report_id' => $report->id,
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at' => now(),
        ]);
        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => 'claimed',
            'new_status' => 'returned',
            'changed_by_user_id' => $admin->id,
            'changed_at' => now(),
            'note' => 'Returned to verified claimant',
        ]);

        $html = view('reports.pdf-single', [
            'report' => $report->fresh()->load(['claims.claimant.profile', 'statusHistory']),
            'generatedAt' => now(),
            'generatedBy' => $admin->email,
            'imageBase64' => null,
            'logoBase64' => null,
        ])->render();
        $this->assertStringContainsString('Returned to verified claimant', $html);

        $response = $this->actingAs($admin)->get(route('reports.exportSinglePdf', $report->id));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
    }

    public function test_regular_users_cannot_download_the_staff_pdf_export(): void
    {
        $report = ItemReport::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('reports.exportSinglePdf', $report->id))->assertForbidden();
    }
}
