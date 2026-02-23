<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $action = trim((string) $request->query('action', ''));
        $entityType = trim((string) $request->query('entity_type', ''));
        $entityId = $request->query('entity_id', '');
        $userId = $request->query('user_id', '');

        $query = ActivityLog::query()->orderByDesc('id');

        if ($action !== '') $query->where('action', $action);
        if ($entityType !== '') $query->where('entity_type', $entityType);
        if ($entityId !== '') $query->where('entity_id', (int) $entityId);
        if ($userId !== '') $query->where('user_id', (int) $userId);

        $logs = $query->paginate(30)->withQueryString();

        return view('activity_logs.index', compact('logs','action','entityType','entityId','userId'));
    }
}
