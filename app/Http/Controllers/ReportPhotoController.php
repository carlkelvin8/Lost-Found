<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;

class ReportPhotoController extends WebBaseController
{
    public function store(Request $request, int $reportId)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($reportId);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($report->isLocked()) {
            if ($request->expectsJson()) throw \Illuminate\Validation\ValidationException::withMessages(['photo' => 'This report is locked and cannot be edited.']);
            return redirect()->route('reports.show', $reportId)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        $data = $request->validate([
            'photo' => ['required', ...\App\Rules\ReportImage::rules()],
            'caption' => ['nullable','string','max:255'],
        ]);

        $url = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $report, $data, &$url) {
                $locked = ItemReport::whereKey($report->id)->lockForUpdate()->firstOrFail();
                if ($locked->isLocked() || $locked->photos()->count() >= 5) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['photo' => 'This report is locked or already has 5 photos.']);
                }
                $url = app(\App\Services\UploadedImageStorage::class)->store($data['photo'], 'report_photos');
                ReportPhoto::create([
                    'report_id' => $report->id,
                    'photo_url' => $url,
                    'caption' => $data['caption'] ?? null,
                    'created_at' => now(),
                ]);
                $this->audit($request, 'photos.create', 'report_photos', null, ['report_id' => $report->id]);
            });
        } catch (\Throwable $exception) {
            if ($url) $this->tryDeletePublicUrlFile($url);
            throw $exception;
        }

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Photo uploaded.');
            return response()->json(['message' => 'Photo uploaded.'], 201);
        }

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
        if ($report->isLocked()) {
            return redirect()->route('reports.show', $report->id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
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
        app(\App\Services\UploadedImageStorage::class)->delete($url);
    }
}
