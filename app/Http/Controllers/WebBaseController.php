<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\ReportStatusHistory;
use Illuminate\Http\Request;

class WebBaseController extends Controller
{
    protected function user()
    {
        return auth()->user();
    }

    protected function hasAnyRole(array $roleNames): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->roles()->whereIn('name', $roleNames)->exists();
    }

    protected function requireAnyRole(array $roleNames): void
    {
        if (!$this->user()) abort(401);
        if (!$this->hasAnyRole($roleNames)) abort(403);
    }

    protected function audit(Request $request, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        ActivityLog::create([
            'user_id' => optional($this->user())->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'meta_json' => $meta ? json_encode($meta) : null,
        ]);
    }

    protected function notify(int $userId, string $type, string $title, string $body, array $data = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'notif_type' => $type,
            'title' => $title,
            'body' => $body,
            'data_json' => $data ? json_encode($data) : null,
        ]);
    }

    protected function pushReportStatus(int $reportId, ?string $old, string $new, ?int $changedByUserId = null, ?string $note = null): void
    {
        ReportStatusHistory::create([
            'report_id' => $reportId,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by_user_id' => $changedByUserId,
            'note' => $note,
            'changed_at' => now(),
        ]);
    }
}
