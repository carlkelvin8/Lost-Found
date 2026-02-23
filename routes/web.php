<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ItemReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportMatchController;
use App\Http\Controllers\ReportPhotoController;
use App\Http\Controllers\ReportStatusHistoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');

Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthWebController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthWebController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
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

    // Users (admin/osa)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.destroy');

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

    // Report photos
    Route::post('/reports/{reportId}/photos', [ReportPhotoController::class, 'store'])->name('reports.photos.store');
    Route::post('/photos/{id}/delete', [ReportPhotoController::class, 'destroy'])->name('photos.destroy');

    // Matches (staff)
    Route::get('/matches', [ReportMatchController::class, 'index'])->name('matches.index');
    Route::post('/matches/{id}/confirm', [ReportMatchController::class, 'confirm'])->name('matches.confirm');
    Route::post('/matches/{id}/reject', [ReportMatchController::class, 'reject'])->name('matches.reject');
    Route::post('/matches/manual', [ReportMatchController::class, 'createManual'])->name('matches.manual');

    // Claims
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
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Logs & History
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('/reports/{reportId}/history', [ReportStatusHistoryController::class, 'index'])->name('reports.history');

    // Test AI Analysis
    Route::get('/test-ai/{reportId}', function ($reportId) {
        if (!auth()->user()->hasAnyRole(['admin', 'osa'])) abort(403);
        
        $report = \App\Models\ItemReport::findOrFail($reportId);
        \App\Jobs\ProcessImageAnalysis::dispatchSync($report->id);
        
        return redirect()->route('reports.show', $reportId)->with('success', 'AI Analysis triggered manually');
    });
});
