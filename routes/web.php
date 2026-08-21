<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'role:super_admin']);

Route::middleware(['auth', 'role:super_admin,spi'])->group(function () {
    Route::resource('audits', AuditPlanController::class);
    Route::resource('findings', FindingController::class);
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::resource('master/divisions', MasterDataController::class);
});

require __DIR__.'/auth.php';
