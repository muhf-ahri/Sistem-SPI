<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditPlanController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\DivisionController;
use App\Http\Controllers\Master\AuditTypeController;
use App\Http\Controllers\Master\FindingCategoryController;
use App\Http\Controllers\Master\RiskCategoryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
// Catatan: registrasi publik dinonaktifkan. Pengguna dibuat oleh Super Admin
// melalui modul Master Data -> Users (SISTEM.md §4).
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password reset routes
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Email verification routes
    Route::get('/email/verify', [\App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/password', [PasswordController::class, 'update'])->name('profile.password.update');
    
    // Password confirmation routes
    Route::get('/confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    
    // Role-based route groups
    
    // Super Admin & SPI routes (full access)
    Route::middleware(['role:super_admin,spi'])->group(function () {
        // Master Data Routes
        Route::prefix('master')->name('master.')->group(function () {
            // Users management
            Route::resource('users', UserController::class);
            
            // Divisions management
            Route::resource('divisions', DivisionController::class);
            
            // Audit types management
            Route::resource('audit-types', AuditTypeController::class);
            
            // Finding categories management
            Route::resource('finding-categories', FindingCategoryController::class);
            
            // Risk categories management
            Route::resource('risk-categories', RiskCategoryController::class);
        });
        
        // Audit Log
        Route::resource('audit-logs', AuditLogController::class);
    });
    
    // SPI, Kepala Divisi, and Management routes (audit management)
    Route::middleware(['role:super_admin,spi,kepala_divisi,management'])->group(function () {
        // Audit Plans
        Route::post('audit-plans/{audit_plan}/start', [AuditPlanController::class, 'startInspection'])->name('audit-plans.start-inspection');
        Route::post('audit-plans/{audit_plan}/complete', [AuditPlanController::class, 'complete'])->name('audit-plans.complete');
        Route::post('audit-plans/{audit_plan}/reactivate', [AuditPlanController::class, 'reactivate'])->name('audit-plans.reactivate');
        Route::post('audit-plans/{audit_plan}/reports', [AuditPlanController::class, 'storeReport'])->name('audit-plans.reports.store');
        Route::get('reports/{report}/download', [AuditPlanController::class, 'downloadReport'])->name('audit-plans.reports.download');
        Route::resource('audit-plans', AuditPlanController::class);
        
        // Inspections
        Route::post('inspections/{inspection}/evidence', [InspectionController::class, 'uploadEvidence'])->name('inspections.upload-evidence');
        Route::resource('inspections', InspectionController::class);

        // Evidence download (force download)
        Route::get('evidence/download/{evidence}', [\App\Http\Controllers\InspectionController::class, 'downloadEvidence'])->name('evidence.download');
        // Evidence delete
        Route::delete('follow-up-evidences/{evidence}', [\App\Http\Controllers\ActionPlanController::class, 'deleteEvidence'])->name('follow-up-evidences.destroy');
        Route::delete('inspection-evidences/{evidence}', [\App\Http\Controllers\InspectionController::class, 'deleteEvidence'])->name('inspection-evidences.destroy');
        
        // Findings
        Route::resource('findings', FindingController::class);
        
        // Action Plans
        Route::post('action-plans/{action_plan}/submit', [ActionPlanController::class, 'submitVerification'])->name('action-plans.submit');
        Route::post('action-plans/{action_plan}/verify', [ActionPlanController::class, 'verify'])->name('action-plans.verify');
        Route::post('action-plans/{action_plan}/evidence', [ActionPlanController::class, 'uploadEvidence'])->name('action-plans.upload-evidence');
        Route::resource('action-plans', ActionPlanController::class);
    });
    
    // Reports (accessible by all authenticated users with appropriate roles)
    Route::middleware(['role:super_admin,spi,kepala_divisi,management'])->group(function () {
        Route::get('/reports/{type}/export/{format}', [\App\Http\Controllers\ReportExportController::class, 'export'])
            ->whereIn('type', ['lha', 'audit-summary', 'finding-analysis', 'action-plan-status'])
            ->whereIn('format', ['excel', 'pdf'])
            ->name('reports.export');
        Route::get('/reports/lha', [\App\Http\Controllers\FinalReportController::class, 'index'])->name('reports.lha');
        Route::delete('/reports/lha/{report}', [\App\Http\Controllers\FinalReportController::class, 'destroy'])->name('reports.lha.destroy');
        Route::get('/reports/audit-summary', [ReportController::class, 'auditSummary'])->name('reports.audit-summary');
        Route::get('/reports/finding-analysis', [ReportController::class, 'findingAnalysis'])->name('reports.finding-analysis');
        Route::get('/reports/action-plan-status', [ReportController::class, 'actionPlanStatus'])->name('reports.action-plan-status');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});