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
        Route::get('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'index'])->name('request.index')->defaults('type', 'in');
        Route::get('/pengajuan/create', [\App\Http\Controllers\Transaction\RequestController::class, 'create'])->name('request.create')->defaults('type', 'in');
        Route::post('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'store'])->name('request.store')->defaults('type', 'in');
        Route::get('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'show'])->name('request.show')->defaults('type', 'in');
        Route::get('/pengajuan/{id}/edit', [\App\Http\Controllers\Transaction\RequestController::class, 'edit'])->name('request.edit')->defaults('type', 'in');
        Route::put('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'update'])->name('request.update')->defaults('type', 'in');
        Route::delete('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'destroy'])->name('request.destroy')->defaults('type', 'in');
        Route::post('/pengajuan/{id}/submit', [\App\Http\Controllers\Transaction\RequestController::class, 'submit'])->name('request.submit')->defaults('type', 'in');
        
        Route::get('/realisasi', fn() => abort(404))->name('transaction.index');
    });

    // Kas Keluar
    Route::prefix('kas-keluar')->name('out.')->group(function () {
        Route::get('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'index'])->name('request.index')->defaults('type', 'out');
        Route::get('/pengajuan/create', [\App\Http\Controllers\Transaction\RequestController::class, 'create'])->name('request.create')->defaults('type', 'out');
        Route::post('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'store'])->name('request.store')->defaults('type', 'out');
        Route::get('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'show'])->name('request.show')->defaults('type', 'out');
        Route::get('/pengajuan/{id}/edit', [\App\Http\Controllers\Transaction\RequestController::class, 'edit'])->name('request.edit')->defaults('type', 'out');
        Route::put('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'update'])->name('request.update')->defaults('type', 'out');
        Route::delete('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'destroy'])->name('request.destroy')->defaults('type', 'out');
        Route::post('/pengajuan/{id}/submit', [\App\Http\Controllers\Transaction\RequestController::class, 'submit'])->name('request.submit')->defaults('type', 'out');

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
        
        // Transaction Templates (Presets)
        Route::post('templates/bulk-delete', [\App\Http\Controllers\Master\TemplateController::class, 'bulkDelete'])->name('templates.bulk-delete');
        Route::resource('templates', \App\Http\Controllers\Master\TemplateController::class);
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
