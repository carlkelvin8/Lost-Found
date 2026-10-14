<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Category;
use App\Models\ItemReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends WebBaseController
{
    /**
     * Export filtered Lost & Found records as PDF (read-only)
     * Reuses existing ItemReport query filters from ItemReportController@index
     */
    public function exportPdf(Request $request)
    {
        $this->requireAnyRole(['admin', 'osa']);

        $type       = (string) $request->query('type', '');
        $status     = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category_id', '');
        $locationId = (string) $request->query('location_id', '');
        $q          = trim((string) $request->query('q', ''));
        $dateFrom   = trim((string) $request->query('date_from', ''));
        $dateTo     = trim((string) $request->query('date_to', ''));

        $query = ItemReport::with(['category', 'location', 'reporter.profile'])
            ->orderByDesc('id');

        if ($type !== '') $query->where('report_type', $type);
        if ($status !== '') $query->where('status', $status);
        if ($categoryId !== '') $query->where('category_id', (int) $categoryId);
        if ($locationId !== '') $query->where('location_id', (int) $locationId);

        // Date range filter on incident_date (preferred per calendar logic) fallback to created_at if incident_date null
        if ($dateFrom !== '' && strtotime($dateFrom) !== false) {
            $query->where(function ($qq) use ($dateFrom) {
                $qq->whereDate('incident_date', '>=', $dateFrom)
                   ->orWhere(function ($q2) use ($dateFrom) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '>=', $dateFrom);
                   });
            });
        }
        if ($dateTo !== '' && strtotime($dateTo) !== false) {
            $query->where(function ($qq) use ($dateTo) {
                $qq->whereDate('incident_date', '<=', $dateTo)
                   ->orWhere(function ($q2) use ($dateTo) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '<=', $dateTo);
                   });
            });
        }

        if ($q !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);
            // Use simple LIKE for PDF export to avoid FULLTEXT dependency and ensure portability
            $query->where(function ($qq) use ($escaped) {
                $qq->where('item_name', 'like', "%{$escaped}%")
                   ->orWhere('item_description', 'like', "%{$escaped}%")
                   ->orWhere('brand_model', 'like', "%{$escaped}%")
                   ->orWhere('color', 'like', "%{$escaped}%")
                   ->orWhere('circumstances', 'like', "%{$escaped}%");
            });
        }

        // Staff sees all, non-staff would be filtered but this route is staff-only, so no reporter filter needed
        $records = $query->limit(500)->get(); // cap for large datasets to avoid PDF memory issues

        // Summary metrics using same filtered query (clone for counts)
        $total = $records->count();
        $totalLost = $records->where('report_type', 'lost')->count();
        $totalFound = $records->where('report_type', 'found')->count();
        $totalClaimed = $records->whereIn('status', ['claimed', 'returned'])->count();
        $totalPending = $records->where('status', 'pending')->count();
        $totalUnclaimed = $records->whereIn('status', ['pending', 'matched'])->count();

        // Handle no records
        if ($total === 0) {
            // Still generate PDF with message rather than broken empty PDF
        }

        $filters = [
            'type' => $type ?: 'All',
            'status' => $status ?: 'All',
            'category' => $categoryId ? (Category::find((int)$categoryId)?->name ?? $categoryId) : 'All',
            'location' => $locationId ? (\App\Models\Location::find((int)$locationId)?->name ?? $locationId) : 'All',
            'search' => $q ?: '—',
            'date_from' => $dateFrom ?: '—',
            'date_to' => $dateTo ?: '—',
        ];

        $generatedAt = now();
        $generatedBy = $this->user()?->email ?? 'System';
        $logoBase64 = $this->getLogoBase64();

        $pdf = Pdf::loadView('reports.pdf', compact(
            'records', 'total', 'totalLost', 'totalFound', 'totalClaimed', 'totalPending', 'totalUnclaimed',
            'filters', 'generatedAt', 'generatedBy', 'logoBase64'
        ))->setPaper('a4', 'landscape');

        // DomPDF options for clean output - professional rendering
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 120,
            'isFontSubsettingEnabled' => true,
        ]);

        $filename = $this->buildFilename($type, $dateFrom, $dateTo);
        return $pdf->download($filename);
    }

    /**
     * Export filtered Lost & Found records as Excel (XLSX)
     * Same filters as exportPdf - professional styled workbook
     */
    public function exportExcel(Request $request)
    {
        $this->requireAnyRole(['admin', 'osa']);

        $type       = (string) $request->query('type', '');
        $status     = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category_id', '');
        $locationId = (string) $request->query('location_id', '');
        $q          = trim((string) $request->query('q', ''));
        $dateFrom   = trim((string) $request->query('date_from', ''));
        $dateTo     = trim((string) $request->query('date_to', ''));

        $query = ItemReport::with(['category', 'location', 'reporter.profile'])
            ->orderByDesc('id');

        if ($type !== '') $query->where('report_type', $type);
        if ($status !== '') $query->where('status', $status);
        if ($categoryId !== '') $query->where('category_id', (int) $categoryId);
        if ($locationId !== '') $query->where('location_id', (int) $locationId);

        if ($dateFrom !== '' && strtotime($dateFrom) !== false) {
            $query->where(function ($qq) use ($dateFrom) {
                $qq->whereDate('incident_date', '>=', $dateFrom)
                   ->orWhere(function ($q2) use ($dateFrom) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '>=', $dateFrom);
                   });
            });
        }
        if ($dateTo !== '' && strtotime($dateTo) !== false) {
            $query->where(function ($qq) use ($dateTo) {
                $qq->whereDate('incident_date', '<=', $dateTo)
                   ->orWhere(function ($q2) use ($dateTo) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '<=', $dateTo);
                   });
            });
        }

        if ($q !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);
            $query->where(function ($qq) use ($escaped) {
                $qq->where('item_name', 'like', "%{$escaped}%")
                   ->orWhere('item_description', 'like', "%{$escaped}%")
                   ->orWhere('brand_model', 'like', "%{$escaped}%")
                   ->orWhere('color', 'like', "%{$escaped}%")
                   ->orWhere('circumstances', 'like', "%{$escaped}%");
            });
        }

        $records = $query->limit(1000)->get(); // higher cap than PDF

        $filters = [
            'type' => $type ?: 'All',
            'status' => $status ?: 'All',
            'category' => $categoryId ? (Category::find((int)$categoryId)?->name ?? $categoryId) : 'All',
            'location' => $locationId ? (\App\Models\Location::find((int)$locationId)?->name ?? $locationId) : 'All',
            'search' => $q ?: '—',
            'date_from' => $dateFrom ?: '—',
            'date_to' => $dateTo ?: '—',
        ];

        $generatedAt = now();
        $generatedBy = $this->user()?->email ?? 'System';

        $export = new ReportsExport($records, $filters, $generatedAt, $generatedBy);
        $filename = $this->buildFilename($type, $dateFrom, $dateTo);
        $filename = preg_replace('/\.pdf$/', '.xlsx', $filename);

        return Excel::download($export, $filename);
    }

    /**
     * Export single item report as PDF
     */
    public function exportSinglePdf(Request $request, int $id)
    {
        $this->requireAnyRole(['admin', 'osa']);

        $report = ItemReport::with(['category', 'location', 'reporter.profile', 'photos', 'claims.claimant.profile', 'statusHistory'])->findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;
        if (!$isStaff && !$isOwner) abort(403);

        $generatedAt = now();
        $generatedBy = $this->user()?->email ?? 'System';
        $logoBase64 = $this->getLogoBase64();

        // Prepare image as base64 if exists for DomPDF inline display
        $imageBase64 = null;
        $firstPhoto = $report->photos->first();
        if ($firstPhoto && $firstPhoto->photo_url) {
            $imageData = app(\App\Services\UploadedImageStorage::class)->read($firstPhoto->photo_url);
            if ($imageData !== null && strlen($imageData) < 1024 * 1024) {
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($imageData) ?: 'image/jpeg';
                $imageBase64 = 'data:'.$mime.';base64,'.base64_encode($imageData);
            }
        }

        $pdf = Pdf::loadView('reports.pdf-single', compact('report', 'generatedAt', 'generatedBy', 'imageBase64', 'logoBase64'))
            ->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 120,
            'isFontSubsettingEnabled' => true,
        ]);

        $safeName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $report->item_name ?? 'item');
        $filename = 'lost-found-'.($report->report_type ?? 'item').'-'.str_pad((string)$report->id, 6, '0', STR_PAD_LEFT).'-'.substr($safeName,0,20).'.pdf';
        $filename = strtolower($filename);

        return $pdf->download($filename);
    }

    private function getLogoBase64(): ?string
    {
        $candidates = [
            public_path('images/naap-logo.png'),
            public_path('image.png'),
            public_path('favicon-32x32.png'),
        ];
        foreach ($candidates as $path) {
            if (file_exists($path) && filesize($path) < 500 * 1024) {
                $mime = mime_content_type($path) ?: 'image/png';
                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
            }
        }
        return null;
    }

    private function buildFilename(string $type, string $dateFrom, string $dateTo): string
    {
        $parts = [];
        if ($type === 'lost') $parts[] = 'lost-items';
        elseif ($type === 'found') $parts[] = 'found-items';
        else $parts[] = 'lost-found-report';

        if ($dateFrom && $dateTo) {
            $parts[] = $dateFrom.'-to-'.$dateTo;
        } elseif ($dateFrom) {
            $parts[] = 'from-'.$dateFrom;
        } elseif ($dateTo) {
            $parts[] = 'to-'.$dateTo;
        } else {
            $parts[] = now()->format('Y-m-d');
        }

        $name = implode('-', $parts) . '.pdf';
        // Sanitize
        $name = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        return strtolower($name);
    }
}
