<?php

namespace App\Jobs;

use App\Models\ItemReport;
use App\Services\MatchService;
use App\Services\OpenAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImageAnalysis implements ShouldQueue
{
    use Queueable;

    protected $reportId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $reportId)
    {
        $this->reportId = $reportId;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $aiService, MatchService $matchService): void
    {
        $report = ItemReport::with('photos')->find($this->reportId);

        if (!$report) {
            return;
        }

        // 1. Get the first photo (for now, analyze main photo)
        $photo = $report->photos->first();
        if (!$photo) {
            return;
        }

        // 2. Analyze Image
        $analysis = $aiService->analyzeImage($photo->photo_url);

        if ($analysis) {
            // 3. Save Analysis
            $report->update(['ai_analysis' => $analysis]);

            // 4. Trigger Matching
            $matchService->findMatches($report);
        }
    }
}
