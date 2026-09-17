<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\ContractorPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// PWA Manifest and Service Worker routes
Route::get('/manifest.webmanifest', function () {
    return response(
        (string) file_get_contents(public_path('assets/pwa/manifest.json')),
        200,
        [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]
    );
})->name('pwa.manifest');

Route::get('/manifest.json', function () {
    return response(
        (string) file_get_contents(public_path('assets/pwa/manifest.json')),
        200,
        [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]
    );
});

Route::get('/offline.html', function () {
    return response(
        (string) file_get_contents(public_path('offline.html')),
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
})->name('pwa.offline');

Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Service-Worker-Allowed' => '/',
    ]);
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Preferences
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/theme', [ProfileController::class, 'updateTheme'])->name('theme.update');

    // Impersonation routes
    Route::post('/impersonate/user/{user}', [ImpersonationController::class, 'impersonate'])->name('impersonate.user');
    Route::post('/impersonate/contractor/{contractor}', [ImpersonationController::class, 'impersonateContractor'])->name('impersonate.contractor');
    Route::match(['get', 'post'], '/impersonate/leave', [ImpersonationController::class, 'leave'])->name('impersonate.leave');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    });

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/contractors', [ProjectController::class, 'assignContractor'])->name('projects.contractors.assign');
    Route::delete('projects/{project}/contractors/{contractor}', [ProjectController::class, 'removeContractor'])->name('projects.contractors.remove');

    // Contractors
    Route::resource('contractors', ContractorController::class);

    // Contractor Payments
    Route::resource('contractor-payments', ContractorPaymentController::class)->except(['edit', 'update']);

    // Workers
    Route::resource('workers', WorkerController::class);

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/daily', [AttendanceController::class, 'storeDaily'])->name('attendance.store-daily');
    Route::get('attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/wages', [ReportController::class, 'wages'])->name('wages');
        Route::get('/contractor-ledger', [ReportController::class, 'contractorLedger'])->name('contractor-ledger');
        Route::get('/project-summary', [ReportController::class, 'projectSummary'])->name('project-summary');
    });

    // Activity Logs
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
});

require __DIR__.'/auth.php';
