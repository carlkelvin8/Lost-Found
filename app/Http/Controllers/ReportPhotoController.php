<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportPhotoController extends WebBaseController
{
    public function store(Request $request, int $reportId)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($reportId);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if (in_array($report->status, ['returned','archived'], true)) {
            return redirect()->route('reports.show', $reportId)->withErrors(['message' => 'Report is locked']);
        }

        $data = $request->validate([
            'photo' => ['required','file','mimes:jpg,jpeg,png,webp','max:4096'],
            'caption' => ['nullable','string','max:255'],
        ]);

        $filename = uniqid() . '_' . $data['photo']->getClientOriginalName();
        $data['photo']->move(public_path('report_photos'), $filename);
        $url = 'report_photos/' . $filename;

        ReportPhoto::create([
            'report_id' => $report->id,
            'photo_url' => $url,
            'caption' => $data['caption'] ?? null,
            'created_at' => now(),
        ]);

        $this->audit($request, 'photos.create', 'report_photos', null, ['report_id' => $report->id]);

        return redirect()->route('reports.show', $reportId)->with('success', 'Uploaded');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $photo = ReportPhoto::findOrFail($id);
        $report = ItemReport::findOrFail((int) $photo->report_id);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if (in_array($report->status, ['returned','archived'], true)) {
            return redirect()->route('reports.show', $report->id)->withErrors(['message' => 'Report is locked']);
        }

        if ($photo->photo_url) {
            $this->tryDeletePublicUrlFile($photo->photo_url);
        }

        $photo->delete();
        $this->audit($request, 'photos.delete', 'report_photos', $id, ['report_id' => $report->id]);

        return redirect()->route('reports.edit', $report->id)->with('success', 'Deleted');
    }

    /**
     * Attempt to delete a file referenced by a public URL.
     */
    private function tryDeletePublicUrlFile(?string $url): void
    {
        if (!$url) return;

        $sanitized = str_replace('\\', '/', $url);
        if (str_contains($sanitized, '..') || str_contains($sanitized, "\0")) {
            return;
        }

        $filePath = public_path($sanitized);
        $resolved = realpath($filePath);
        $base = realpath(public_path());
        if ($resolved && $base && str_starts_with($resolved, $base) && file_exists($resolved)) {
            @unlink($resolved);
            return;
        }

        if (str_starts_with($sanitized, 'storage/')) {
            $relative = substr($sanitized, strlen('storage/'));
            $oldPath = public_path($relative);
            $oldResolved = realpath($oldPath);
            if ($oldResolved && $base && str_starts_with($oldResolved, $base) && file_exists($oldResolved)) {
                @unlink($oldResolved);
            }
        }
    }
}
