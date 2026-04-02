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
        Route::post('categories/bulk-delete', [\App\Http\Controllers\Master\CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::resource('categories', \App\Http\Controllers\Master\CategoryController::class);
        Route::resource('users', \App\Http\Controllers\Master\UserController::class);
        Route::get('/template', fn() => abort(404))->name('template.index');
        Route::get('/group-akses', fn() => abort(404))->name('group.index');
    });

    // Pengaturan
    Route::get('/pengaturan', fn() => abort(404))->name('settings.index');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
