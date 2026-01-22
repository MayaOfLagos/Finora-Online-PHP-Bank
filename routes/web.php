<?php

use App\Http\Controllers\ImpersonationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Impersonation routes
    Route::post('/admin/stop-impersonation', [ImpersonationController::class, 'stop'])->name('admin.stop-impersonation');
});
