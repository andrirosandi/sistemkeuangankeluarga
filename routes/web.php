<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Setup Wizard Routes
| Hanya bisa diakses saat belum ada user di database (dijaga middleware global)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('setup.guard')->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup/admin', [SetupController::class, 'storeAdmin'])->name('setup.admin');
    Route::post('/setup/settings', [SetupController::class, 'storeSettings'])->name('setup.settings');
    Route::post('/setup/mail', [SetupController::class, 'storeMail'])->name('setup.mail');
});

/*
|--------------------------------------------------------------------------
| Authenticated Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (dari Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Placeholder routes (akan diisi saat module dikerjakan)
    Route::get('/notifications', fn() => abort(404))->name('notification.index');

    // Kas Masuk
    Route::prefix('kas-masuk')->name('in.')->group(function () {
        Route::get('/pengajuan', fn() => abort(404))->name('request.index');
        Route::get('/realisasi', fn() => abort(404))->name('transaction.index');
    });

    // Kas Keluar
    Route::prefix('kas-keluar')->name('out.')->group(function () {
        Route::get('/pengajuan', fn() => abort(404))->name('request.index');
        Route::get('/realisasi', fn() => abort(404))->name('transaction.index');
    });

    // Mutasi
    Route::get('/mutasi', fn() => abort(404))->name('mutation.index');

    // Laporan
    Route::get('/laporan', fn() => abort(404))->name('report.index');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Categories
        Route::post('categories/bulk-delete', [\App\Http\Controllers\Master\CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::resource('categories', \App\Http\Controllers\Master\CategoryController::class);

        // Users
        Route::post('users/bulk-delete', [\App\Http\Controllers\Master\UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::put('users/{user}/reset-password', [\App\Http\Controllers\Master\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('users', \App\Http\Controllers\Master\UserController::class);
        
        // Roles & Permissions
        Route::post('roles/bulk-delete', [\App\Http\Controllers\Master\RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
        Route::resource('roles', \App\Http\Controllers\Master\RoleController::class);
        
        Route::get('/template', fn() => abort(404))->name('template.index');
    });

    // Pengaturan
    Route::get('/pengaturan', [\App\Http\Controllers\Master\SettingController::class, 'index'])->name('settings.index');
    Route::post('/pengaturan', [\App\Http\Controllers\Master\SettingController::class, 'update'])->name('settings.update');
    Route::post('/pengaturan/verify-otp', [\App\Http\Controllers\Master\SettingController::class, 'verifyOtp'])->name('settings.verify-otp');

    // Dedicated Upload API
    Route::post('/api/upload-media', [\App\Http\Controllers\Api\UploadController::class, 'store'])->name('api.upload');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
