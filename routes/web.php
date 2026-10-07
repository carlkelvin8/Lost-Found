<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ItemReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ReportMatchController;
use App\Http\Controllers\ReportPhotoController;
use App\Http\Controllers\ReportStatusHistoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

// Legal pages
Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Offline page
Route::get('/offline', function () {
    return response()->file(public_path('offline.html'));
})->name('offline');

// Storage file serving (for Hostinger without symlink support)
Route::get('/storage/{path}', [StorageController::class, 'serve'])
    ->where('path', '.*')
    ->middleware('auth')
    ->name('storage.serve');

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');

Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthWebController::class, 'register'])
    ->middleware('throttle:3,1')
    ->name('register.post');

// Email Verification Routes (OTP-based)
Route::get('/email/verify', [AuthWebController::class, 'showVerifyEmail'])
    ->middleware('auth')
    ->name('verification.notice');

Route::post('/email/verify', [AuthWebController::class, 'verifyEmail'])
    ->middleware('auth')
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthWebController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Password Reset Routes
Route::get('/forgot-password', [AuthWebController::class, 'showForgotPassword'])
    ->name('password.request');

Route::post('/forgot-password', [AuthWebController::class, 'sendResetLink'])
    ->middleware('throttle:3,1')
    ->name('password.email');

Route::get('/reset-password/{token}', [AuthWebController::class, 'showResetPassword'])
    ->name('password.reset');

Route::post('/reset-password', [AuthWebController::class, 'resetPassword'])
    ->middleware('throttle:5,1')
    ->name('password.update');

// Dev helper: view last reset email link when MAIL_MAILER=log (local only)
Route::get('/dev/last-email', function () {
    if (!app()->environment('local')) abort(404);
    $log = @file_get_contents(storage_path('logs/laravel.log'));
    if (!$log) return response('No log file', 404);
    preg_match_all('#http://[^"\s<\']+/reset-password/[^"\s<\']+#', $log, $m);
    $links = array_reverse($m[0] ?? []);
    $links = array_unique(array_map(fn($u)=> html_entity_decode($u), $links));
    $links = array_slice($links, 0, 5);
    if (empty($links)) return response('<h3>No reset links found in log</h3><p>Try POST /forgot-password first. Check storage/logs/laravel.log</p>', 200, ['Content-Type'=>'text/html']);
    $html = '<h2>Last 5 Reset Links (MAIL_MAILER=log)</h2><ul>';
    foreach ($links as $l) $html .= '<li><a href="'.e($l).'" target="_blank">'.e($l).'</a></li>';
    $html .= '</ul><p><a href="'.route('password.request').'">Back to forgot password</a></p>';
    return response($html, 200, ['Content-Type'=>'text/html']);
})->name('dev.last-email');

Route::post('/logout', [AuthWebController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // My profile
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');

    // Reference tables
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::post('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::post('/departments/{id}/delete', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::post('/roles/{id}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{id}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::post('/locations/{id}', [LocationController::class, 'update'])->name('locations.update');
    Route::post('/locations/{id}/delete', [LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Users (admin/osa) - Account Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // Staff Tools - admin account creation (requires e-mail verification)
    Route::get('/users/create-admin', [UserController::class, 'createAdmin'])->name('users.createAdmin');
    Route::post('/users/create-admin', [UserController::class, 'storeAdmin'])->name('users.storeAdmin');

    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');

    // Reports - PDF/Excel Export (must be before {id} to avoid capture)
    Route::get('/reports/export/pdf', [ReportExportController::class, 'exportPdf'])->name('reports.exportPdf');
    Route::get('/reports/export/excel', [ReportExportController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('/reports/{id}/export/pdf', [ReportExportController::class, 'exportSinglePdf'])->name('reports.exportSinglePdf');

    // Reports
    Route::get('/reports', [ItemReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ItemReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ItemReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{id}', [ItemReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{id}/edit', [ItemReportController::class, 'edit'])->name('reports.edit');
    Route::post('/reports/{id}', [ItemReportController::class, 'update'])->name('reports.update');
    Route::post('/reports/{id}/status', [ItemReportController::class, 'setStatus'])->name('reports.status');
    Route::post('/reports/{id}/mark-returned', [ItemReportController::class, 'markReturned'])->name('reports.markReturned');
    Route::post('/reports/{id}/archive', [ItemReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{id}/lock', [ItemReportController::class, 'setLocked'])->name('reports.lock');

    // Report photos
    Route::post('/reports/{reportId}/photos', [ReportPhotoController::class, 'store'])->name('reports.photos.store');
    Route::post('/photos/{id}/delete', [ReportPhotoController::class, 'destroy'])->name('photos.destroy');

    // Matches (staff)
    Route::get('/matches', [ReportMatchController::class, 'index'])->name('matches.index');
    Route::post('/matches/{id}/confirm', [ReportMatchController::class, 'confirm'])->name('matches.confirm');
    Route::post('/matches/{id}/reject', [ReportMatchController::class, 'reject'])->name('matches.reject');
    Route::post('/matches/manual', [ReportMatchController::class, 'createManual'])->name('matches.manual');

    // Claims
    Route::get('/claims/successful', [ClaimController::class, 'successful'])->name('claims.successful');
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/create/{reportId}', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/claims/{id}', [ClaimController::class, 'show'])->name('claims.show');
    Route::post('/claims/{id}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{id}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
    Route::post('/claims/{id}/cancel', [ClaimController::class, 'cancel'])->name('claims.cancel');

    // Claim documents
    Route::post('/claims/{claimId}/documents', [ClaimDocumentController::class, 'store'])->name('claim_docs.store');
    Route::post('/claim-documents/{id}/delete', [ClaimDocumentController::class, 'destroy'])->name('claim_docs.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/check', [NotificationController::class, 'check'])->name('notifications.check');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Presence (online status)
    Route::post('/presence/heartbeat', function () {
        \Illuminate\Support\Facades\Cache::put(
            'user_online_' . auth()->id(),
            true,
            now()->addMinutes(2)
        );
        return response()->json(['ok' => true]);
    });

    Route::post('/presence/leave', function () {
        \Illuminate\Support\Facades\Cache::forget('user_online_' . auth()->id());
        return response()->json(['ok' => true]);
    });

    // Bulk operations (staff only)
    Route::delete('/reports/bulk-delete', function () {
        $user = auth()->user();
        $isStaff = $user->hasAnyRole(['admin', 'osa']);
        $ids = request()->input('ids', []);

        if (empty($ids)) {
            return response()->json(['ok' => true]);
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return response()->json(['error' => 'Invalid IDs'], 422);
        }

        $query = \App\Models\ItemReport::whereIn('id', $ids);
        if (!$isStaff) {
            $query->where('reporter_user_id', $user->id);
        }
        $query->delete();

        return response()->json(['ok' => true]);
    });

    Route::patch('/reports/bulk-status', function () {
        $user = auth()->user();
        $isStaff = $user->hasAnyRole(['admin', 'osa']);
        $ids = request()->input('ids', []);
        $status = request()->input('status');

        if (!$isStaff) {
            abort(403);
        }

        if (empty($ids)) {
            return response()->json(['ok' => true]);
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return response()->json(['error' => 'Invalid IDs'], 422);
        }

        $allowedStatuses = ['pending', 'matched', 'claimed', 'returned', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            abort(422, 'Invalid status');
        }

        \App\Models\ItemReport::whereIn('id', $ids)->update(['status' => $status]);
        return response()->json(['ok' => true]);
    });

    // Report comments (using activity_logs table)
    Route::get('/reports/{reportId}/comments', function ($reportId) {
        $comments = \App\Models\ActivityLog::where('subject_type', 'App\Models\ItemReport')
            ->where('subject_id', $reportId)
            ->where('event', 'comment')
            ->with('causer')
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'text' => $log->description,
                    'type' => $log->properties['type'] ?? 'comment',
                    'user_id' => $log->causer_id,
                    'user' => ['name' => $log->causer->name ?? 'Unknown'],
                    'created_at' => $log->created_at
                ];
            });
        return response()->json($comments);
    });

    Route::post('/reports/{reportId}/comments', function ($reportId) {
        $validated = request()->validate([
            'text' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        $report = \App\Models\ItemReport::findOrFail($reportId);
        $log = \App\Models\ActivityLog::create([
            'event' => 'comment',
            'subject_type' => 'App\Models\ItemReport',
            'subject_id' => $reportId,
            'causer_id' => auth()->id(),
            'description' => $validated['text'],
            'properties' => ['type' => $validated['type'] ?? 'comment']
        ]);
        return response()->json([
            'id' => $log->id,
            'text' => $log->description,
            'type' => $log->properties['type'],
            'user_id' => $log->causer_id,
            'user' => ['name' => auth()->user()->name],
            'created_at' => $log->created_at
        ]);
    });

    Route::delete('/reports/{reportId}/comments/{commentId}', function ($reportId, $commentId) {
        \App\Models\ActivityLog::where('id', $commentId)
            ->where('causer_id', auth()->id())
            ->where('event', 'comment')
            ->delete();
        return response()->json(['ok' => true]);
    });

    // Leaderboard
    Route::get('/leaderboard', function () {
        $users = \App\Models\User::withCount(['reports', 'claims'])
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'reports_count' => $user->reports_count,
                    'claims_count' => $user->claims_count,
                    'score' => ($user->reports_count * 10) + ($user->claims_count * 5)
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(10);
        return response()->json($users);
    });

    // Dashboard stats API (for real-time refresh)
    Route::get('/dashboard/stats', function () {
        $user = auth()->user();
        return response()->json([
            'reports_count' => \App\Models\ItemReport::where('reporter_user_id', $user->id)->count(),
            'claims_count' => \App\Models\Claim::where('claimant_user_id', $user->id)->count(),
            'pending_count' => \App\Models\ItemReport::where('reporter_user_id', $user->id)->where('status', 'pending')->count(),
            'total_users' => \App\Models\User::count(),
            'total_reports' => \App\Models\ItemReport::count(),
        ]);
    });

    // Bulk export
    Route::get('/reports/bulk-export', function () {
        $user = auth()->user();
        $isStaff = $user->hasAnyRole(['admin', 'osa']);
        $ids = request()->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 422);
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return response()->json(['error' => 'Invalid IDs'], 422);
        }

        $query = \App\Models\ItemReport::whereIn('id', $ids);
        if (!$isStaff) {
            $query->where('reporter_user_id', $user->id);
        }
        $reports = $query->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', 'Item Name', 'Type', 'Status', 'Created At']);

        foreach ($reports as $report) {
            fputcsv($handle, [
                $report->id,
                $report->item_name,
                $report->report_type,
                $report->status,
                $report->created_at,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="reports_export.csv"');
    });

    // Session management (admin only)
    Route::delete('/sessions/{sessionId}/revoke', function ($sessionId) {
        if (!auth()->user()->hasAnyRole(['admin', 'osa'])) {
            abort(403);
        }
        // For now, just return success (would need session tracking in DB)
        return response()->json(['ok' => true]);
    });

    // Push subscription
    Route::post('/push/subscribe', function () {
        $endpoint = request()->input('endpoint');
        // Store push subscription (would need push_subscriptions table)
        return response()->json(['ok' => true]);
    });

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/counts', [CalendarController::class, 'counts'])->name('calendar.counts');
    Route::get('/calendar/day', [CalendarController::class, 'day'])->name('calendar.day');

    // Logs & History
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('/reports/{reportId}/history', [ReportStatusHistoryController::class, 'index'])->name('reports.history');

    // Test AI Analysis
    Route::post('/test-ai/{reportId}', function ($reportId) {
        if (!auth()->user()->hasAnyRole(['admin', 'osa'])) abort(403);
        
        $report = \App\Models\ItemReport::findOrFail($reportId);
        \App\Jobs\ProcessImageAnalysis::dispatch($report->id);
        
        return redirect()->route('reports.show', $reportId)->with('success', 'AI Analysis queued for processing');
    })->name('test-ai');

});

