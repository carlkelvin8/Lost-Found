<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ItemReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends WebBaseController
{
    /**
     * Calendar page — monthly view with filters.
     */
    public function index(Request $request)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $categories = Cache::remember('categories_list', 3600, fn() =>
            Category::orderBy('name')->get()
        );

        // Distinct statuses from item_reports + known set to avoid empty filter
        $knownStatuses = ['pending', 'matched', 'claimed', 'returned', 'archived'];
        $dbStatuses = ItemReport::select('status')->distinct()->pluck('status')->toArray();
        $statuses = array_values(array_unique(array_merge($knownStatuses, $dbStatuses)));
        sort($statuses);

        return view('calendar.index', compact('categories', 'statuses'));
    }

    /**
     * GET /calendar/counts?year=2026&month=9&type=&category_id=&status=&start=YYYY-MM-DD&end=YYYY-MM-DD
     * Returns aggregated counts per date for the visible calendar range.
     * Performance: single aggregated query grouped by incident_date + report_type.
     */
    public function counts(Request $request)
    {
        if (!$this->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'year'        => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'       => ['nullable', 'integer', 'min:1', 'max:12'],
            'start'       => ['nullable', 'date_format:Y-m-d'],
            'end'         => ['nullable', 'date_format:Y-m-d'],
            'type'        => ['nullable', 'string', 'in:lost,found'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status'      => ['nullable', 'string', 'in:pending,matched,claimed,returned,archived'],
        ]);

        // Determine date range: prefer explicit start/end (grid range), fallback to year/month
        if (!empty($validated['start']) && !empty($validated['end'])) {
            try {
                $start = Carbon::parse($validated['start'])->startOfDay();
                $end   = Carbon::parse($validated['end'])->endOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date range'], 422);
            }
            // Guard: limit range to ~62 days to prevent abuse
            if ($start->diffInDays($end) > 62) {
                return response()->json(['error' => 'Date range too large'], 422);
            }
        } else {
            $year  = (int) ($validated['year'] ?? now()->year);
            $month = (int) ($validated['month'] ?? now()->month);
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end   = (clone $start)->endOfMonth()->endOfDay();
        }

        $startDate = $start->format('Y-m-d');
        $endDate   = $end->format('Y-m-d');

        $query = ItemReport::query()
            ->whereNotNull('incident_date')
            ->whereBetween('incident_date', [$startDate, $endDate]);

        // Apply filters — must match detail query logic for consistency
        if (!empty($validated['type'])) {
            $query->where('report_type', $validated['type']);
        }
        if (!empty($validated['category_id'])) {
            $query->where('category_id', (int) $validated['category_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Single efficient aggregated query
        $rows = $query
            ->select('incident_date', 'report_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('incident_date', 'report_type')
            ->get();

        // Build map: date_string => [lost, found, total]
        $map = [];
        foreach ($rows as $row) {
            $dateKey = Carbon::parse($row->incident_date)->format('Y-m-d');
            if (!isset($map[$dateKey])) {
                $map[$dateKey] = ['lost' => 0, 'found' => 0, 'total' => 0];
            }
            if ($row->report_type === 'lost') {
                $map[$dateKey]['lost'] = (int) $row->cnt;
            } elseif ($row->report_type === 'found') {
                $map[$dateKey]['found'] = (int) $row->cnt;
            }
        }
        foreach ($map as $d => $v) {
            $map[$d]['total'] = $v['lost'] + $v['found'];
        }

        return response()->json([
            'start'  => $startDate,
            'end'    => $endDate,
            'counts' => $map,
        ]);
    }

    /**
     * GET /calendar/day?date=YYYY-MM-DD&type=&category_id=&status=
     * Returns all reports for a specific incident_date, split into lost/found.
     */
    public function day(Request $request)
    {
        if (!$this->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'date'        => ['required', 'date_format:Y-m-d'],
            'type'        => ['nullable', 'string', 'in:lost,found'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status'      => ['nullable', 'string', 'in:pending,matched,claimed,returned,archived'],
        ]);

        $date = $validated['date'];

        $query = ItemReport::with(['category', 'location', 'photos', 'reporter'])
            ->where('incident_date', $date);

        if (!empty($validated['type'])) {
            $query->where('report_type', $validated['type']);
        }
        if (!empty($validated['category_id'])) {
            $query->where('category_id', (int) $validated['category_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $reports = $query->orderByDesc('id')->get();

        $lostItems  = [];
        $foundItems = [];

        foreach ($reports as $r) {
            $photoUrl = null;
            $firstPhoto = $r->photos->first();
            if ($firstPhoto && $firstPhoto->photo_url) {
                $photoUrl = uploaded_image_url($firstPhoto->photo_url);
            }

            $payload = [
                'id'               => $r->id,
                'item_name'        => $r->item_name,
                'category'         => $r->category?->name,
                'category_id'      => $r->category_id,
                'item_description' => $r->item_description,
                'brand_model'      => $r->brand_model,
                'color'            => $r->color,
                'location'         => $r->location?->name,
                'location_id'      => $r->location_id,
                'incident_date'    => $r->incident_date ? Carbon::parse($r->incident_date)->format('Y-m-d') : null,
                'incident_time'    => $r->incident_time,
                'status'           => $r->status,
                'report_type'      => $r->report_type,
                'reporter'         => $r->reporter?->name ?? $r->reporter?->email ?? null,
                'reporter_id'      => $r->reporter_user_id,
                'photo_url'        => $photoUrl,
                'circumstances'    => $r->circumstances,
                'detail_url'       => route('reports.show', $r->id),
            ];

            if ($r->report_type === 'lost') {
                $lostItems[] = $payload;
            } else {
                $foundItems[] = $payload;
            }
        }

        return response()->json([
            'date'       => $date,
            'lost_count' => count($lostItems),
            'found_count'=> count($foundItems),
            'total'      => count($lostItems) + count($foundItems),
            'lost_items' => $lostItems,
            'found_items'=> $foundItems,
        ]);
    }
}
