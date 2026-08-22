<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
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

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Registration routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
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
        Route::resource('audit-plans', AuditPlanController::class);
        
        // Inspections
        Route::post('inspections/{inspection}/evidence', [InspectionController::class, 'uploadEvidence'])->name('inspections.upload-evidence');
        Route::resource('inspections', InspectionController::class);
        
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
        Route::get('/reports/audit-summary', [ReportController::class, 'auditSummary'])->name('reports.audit-summary');
        Route::get('/reports/finding-analysis', [ReportController::class, 'findingAnalysis'])->name('reports.finding-analysis');
        Route::get('/reports/action-plan-status', [ReportController::class, 'actionPlanStatus'])->name('reports.action-plan-status');
    });
});