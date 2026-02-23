<?php

namespace App\Services;

use App\Models\ItemReport;
use App\Models\ReportMatch;
use Illuminate\Support\Facades\Log;

class MatchService
{
    /**
     * Find matches for a given report based on AI analysis.
     *
     * @param ItemReport $report
     * @return void
     */
    public function findMatches(ItemReport $report): void
    {
        $analysis = $report->ai_analysis;
        
        // If no AI analysis, we can't do AI matching (fallback to basic matching could be here)
        if (!$analysis || !is_array($analysis)) {
            return;
        }

        $keywords = $analysis['keywords'] ?? [];
        $color = $analysis['color'] ?? '';
        $brand = $analysis['brand'] ?? '';

        // Determine target type (Lost -> looks for Found; Found -> looks for Lost)
        $targetType = ($report->report_type === 'lost') ? 'found' : 'lost';

        // Get potential candidates
        // Optimization: Filter by category if available, or recent reports
        $candidates = ItemReport::where('report_type', $targetType)
            ->whereIn('status', ['pending', 'matched', 'claimed']) // Active statuses
            ->whereNotNull('ai_analysis') // Only compare with analyzed reports
            ->get();

        foreach ($candidates as $candidate) {
            $score = $this->calculateMatchScore($analysis, $candidate->ai_analysis);

            if ($score >= 70) { // Threshold
                $this->createMatch($report, $candidate, $score);
            }
        }
    }

    private function calculateMatchScore(array $source, array $target): int
    {
        $score = 0;
        
        // 1. Color Match (Simple string comparison for MVP)
        // In production, use vector embeddings or color distance
        if (isset($source['color'], $target['color'])) {
            if (str_contains(strtolower($source['color']), strtolower($target['color'])) || 
                str_contains(strtolower($target['color']), strtolower($source['color']))) {
                $score += 20;
            }
        }

        // 2. Brand Match
        if (isset($source['brand'], $target['brand']) && $source['brand'] && $target['brand']) {
            if (strcasecmp($source['brand'], $target['brand']) === 0) {
                $score += 30;
            }
        }

        // 3. Keyword Overlap
        $sourceKeywords = array_map('strtolower', $source['keywords'] ?? []);
        $targetKeywords = array_map('strtolower', $target['keywords'] ?? []);
        
        if (!empty($sourceKeywords) && !empty($targetKeywords)) {
            $intersection = array_intersect($sourceKeywords, $targetKeywords);
            $overlapCount = count($intersection);
            
            // Add 10 points per matching keyword, up to 50
            $score += min($overlapCount * 10, 50);
        }

        return min($score, 100);
    }

    private function createMatch(ItemReport $source, ItemReport $candidate, int $score): void
    {
        $lostId = ($source->report_type === 'lost') ? $source->id : $candidate->id;
        $foundId = ($source->report_type === 'found') ? $source->id : $candidate->id;

        // Check if match already exists
        $exists = ReportMatch::where('lost_report_id', $lostId)
            ->where('found_report_id', $foundId)
            ->exists();

        if (!$exists) {
            ReportMatch::create([
                'lost_report_id' => $lostId,
                'found_report_id' => $foundId,
                'score' => $score,
                'method' => 'ai_image_recognition',
                'status' => 'suggested'
            ]);
            
            Log::info("Match found via AI: Lost #$lostId <-> Found #$foundId (Score: $score)");
        }
    }
}
