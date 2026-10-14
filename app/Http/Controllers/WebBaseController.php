<?php

namespace App\Http\Controllers;

use App\Mail\NotificationMail;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        if ((int) ($u->is_active ?? 1) !== 1) return false;

        return $u->roles()->whereIn('name', $roleNames)->exists();
    }

    protected function requireAnyRole(array $roleNames): void
    {
        $u = $this->user();
        if (!$u) abort(401);
        if ((int) ($u->is_active ?? 1) !== 1) abort(403, 'Account disabled');
        if (!$this->hasAnyRole($roleNames)) abort(403);
    }

    /**
     * Enforce that inactive accounts cannot access any guarded admin functions.
     * Used as helper for controllers that need explicit active check.
     */
    protected function requireActive(): void
    {
        $u = $this->user();
        if (!$u) abort(401);
        if ((int) ($u->is_active ?? 1) !== 1) abort(403, 'Account disabled');
    }

    protected function audit(Request $request, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        try {
            ActivityLog::create([
                'user_id' => optional($this->user())->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'meta_json' => $meta ? json_encode($meta) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('audit failed: ' . $e->getMessage(), ['action' => $action]);
        }
    }

    protected function notify(int $userId, string $type, string $title, string $body, array $data = []): void
    {
        // Save to database
        Notification::create([
            'user_id' => $userId,
            'notif_type' => $type,
            'title' => $title,
            'body' => $body,
            'data_json' => $data ? json_encode($data) : null,
        ]);

        // Only send committed notifications; retries must not duplicate email side effects.
        \Illuminate\Support\Facades\DB::afterCommit(function () use ($userId, $title, $body, $type) {
            try {
                $user = User::find($userId);
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new NotificationMail($title, $body, $type));
                }
            } catch (\Throwable $e) {
                Log::warning('Email notification failed', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        });
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
