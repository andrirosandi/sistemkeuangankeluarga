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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');

    // Profile (dari Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Placeholder routes (akan diisi saat module dikerjakan)
    Route::get('/notifications', fn() => abort(404))->name('notification.index')->middleware('can:notification.view');

    // Kas Masuk
    Route::prefix('kas-masuk')->name('in.')->group(function () {
        // Pengajuan
        Route::get('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'index'])->name('request.index')->defaults('type', 'in')->middleware('can:in.request.view');
        Route::get('/pengajuan/create', [\App\Http\Controllers\Transaction\RequestController::class, 'create'])->name('request.create')->defaults('type', 'in')->middleware('can:in.request.create');
        Route::post('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'store'])->name('request.store')->defaults('type', 'in')->middleware('can:in.request.create');
        Route::get('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'show'])->name('request.show')->defaults('type', 'in')->middleware('can:in.request.view');
        Route::get('/pengajuan/{id}/edit', [\App\Http\Controllers\Transaction\RequestController::class, 'edit'])->name('request.edit')->defaults('type', 'in')->middleware('can:in.request.edit');
        Route::put('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'update'])->name('request.update')->defaults('type', 'in')->middleware('can:in.request.edit');
        Route::delete('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'destroy'])->name('request.destroy')->defaults('type', 'in')->middleware('can:in.request.delete');
        Route::post('/pengajuan/{id}/submit', [\App\Http\Controllers\Transaction\RequestController::class, 'submit'])->name('request.submit')->defaults('type', 'in')->middleware('can:in.request.edit');
        Route::post('/pengajuan/{id}/approve', [\App\Http\Controllers\Transaction\RequestController::class, 'approve'])->name('request.approve')->defaults('type', 'in')->middleware('can:in.request.approve');
        Route::post('/pengajuan/{id}/reject', [\App\Http\Controllers\Transaction\RequestController::class, 'reject'])->name('request.reject')->defaults('type', 'in')->middleware('can:in.request.approve');

        // Realisasi
        Route::get('/realisasi', [\App\Http\Controllers\Transaction\TransactionController::class, 'index'])->name('transaction.index')->defaults('type', 'in')->middleware('can:in.transaction.view');
        Route::get('/realisasi/create', [\App\Http\Controllers\Transaction\TransactionController::class, 'create'])->name('transaction.create')->defaults('type', 'in')->middleware('can:in.transaction.create');
        Route::post('/realisasi', [\App\Http\Controllers\Transaction\TransactionController::class, 'store'])->name('transaction.store')->defaults('type', 'in')->middleware('can:in.transaction.create');
        Route::get('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'show'])->name('transaction.show')->defaults('type', 'in')->middleware('can:in.transaction.view');
        Route::get('/realisasi/{id}/edit', [\App\Http\Controllers\Transaction\TransactionController::class, 'edit'])->name('transaction.edit')->defaults('type', 'in')->middleware('can:in.transaction.edit');
        Route::put('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'update'])->name('transaction.update')->defaults('type', 'in')->middleware('can:in.transaction.edit');
        Route::post('/realisasi/{id}/complete', [\App\Http\Controllers\Transaction\TransactionController::class, 'complete'])->name('transaction.complete')->defaults('type', 'in')->middleware('can:in.transaction.edit');
        Route::post('/realisasi/{id}/cancel', [\App\Http\Controllers\Transaction\TransactionController::class, 'cancel'])->name('transaction.cancel')->defaults('type', 'in')->middleware('can:in.transaction.edit');
        Route::delete('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'destroy'])->name('transaction.destroy')->defaults('type', 'in')->middleware('can:in.transaction.delete');
    });

    // Kas Keluar
    Route::prefix('kas-keluar')->name('out.')->group(function () {
        // Pengajuan
        Route::get('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'index'])->name('request.index')->defaults('type', 'out')->middleware('can:out.request.view');
        Route::get('/pengajuan/create', [\App\Http\Controllers\Transaction\RequestController::class, 'create'])->name('request.create')->defaults('type', 'out')->middleware('can:out.request.create');
        Route::post('/pengajuan', [\App\Http\Controllers\Transaction\RequestController::class, 'store'])->name('request.store')->defaults('type', 'out')->middleware('can:out.request.create');
        Route::get('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'show'])->name('request.show')->defaults('type', 'out')->middleware('can:out.request.view');
        Route::get('/pengajuan/{id}/edit', [\App\Http\Controllers\Transaction\RequestController::class, 'edit'])->name('request.edit')->defaults('type', 'out')->middleware('can:out.request.edit');
        Route::put('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'update'])->name('request.update')->defaults('type', 'out')->middleware('can:out.request.edit');
        Route::delete('/pengajuan/{id}', [\App\Http\Controllers\Transaction\RequestController::class, 'destroy'])->name('request.destroy')->defaults('type', 'out')->middleware('can:out.request.delete');
        Route::post('/pengajuan/{id}/submit', [\App\Http\Controllers\Transaction\RequestController::class, 'submit'])->name('request.submit')->defaults('type', 'out')->middleware('can:out.request.edit');
        Route::post('/pengajuan/{id}/approve', [\App\Http\Controllers\Transaction\RequestController::class, 'approve'])->name('request.approve')->defaults('type', 'out')->middleware('can:out.request.approve');
        Route::post('/pengajuan/{id}/reject', [\App\Http\Controllers\Transaction\RequestController::class, 'reject'])->name('request.reject')->defaults('type', 'out')->middleware('can:out.request.approve');

        // Realisasi
        Route::get('/realisasi', [\App\Http\Controllers\Transaction\TransactionController::class, 'index'])->name('transaction.index')->defaults('type', 'out')->middleware('can:out.transaction.view');
        Route::get('/realisasi/create', [\App\Http\Controllers\Transaction\TransactionController::class, 'create'])->name('transaction.create')->defaults('type', 'out')->middleware('can:out.transaction.create');
        Route::post('/realisasi', [\App\Http\Controllers\Transaction\TransactionController::class, 'store'])->name('transaction.store')->defaults('type', 'out')->middleware('can:out.transaction.create');
        Route::get('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'show'])->name('transaction.show')->defaults('type', 'out')->middleware('can:out.transaction.view');
        Route::get('/realisasi/{id}/edit', [\App\Http\Controllers\Transaction\TransactionController::class, 'edit'])->name('transaction.edit')->defaults('type', 'out')->middleware('can:out.transaction.edit');
        Route::put('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'update'])->name('transaction.update')->defaults('type', 'out')->middleware('can:out.transaction.edit');
        Route::post('/realisasi/{id}/complete', [\App\Http\Controllers\Transaction\TransactionController::class, 'complete'])->name('transaction.complete')->defaults('type', 'out')->middleware('can:out.transaction.edit');
        Route::post('/realisasi/{id}/cancel', [\App\Http\Controllers\Transaction\TransactionController::class, 'cancel'])->name('transaction.cancel')->defaults('type', 'out')->middleware('can:out.transaction.edit');
        Route::delete('/realisasi/{id}', [\App\Http\Controllers\Transaction\TransactionController::class, 'destroy'])->name('transaction.destroy')->defaults('type', 'out')->middleware('can:out.transaction.delete');
    });

    // Mutasi
    Route::prefix('mutasi')->name('mutation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Transaction\MutationController::class, 'index'])->name('index')->middleware('can:mutation.view');
        Route::get('/print', [\App\Http\Controllers\Transaction\MutationController::class, 'index'])->name('print')->middleware('can:mutation.view');
    });

    // Laporan
    Route::get('/laporan', fn() => abort(404))->name('report.index')->middleware('can:report.view');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Categories
        Route::post('categories/bulk-delete', [\App\Http\Controllers\Master\CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete')->middleware('can:category.delete');
        Route::resource('categories', \App\Http\Controllers\Master\CategoryController::class);

        // Users
        Route::post('users/bulk-delete', [\App\Http\Controllers\Master\UserController::class, 'bulkDelete'])->name('users.bulk-delete')->middleware('can:user.delete');
        Route::put('users/{user}/reset-password', [\App\Http\Controllers\Master\UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('can:user.reset-password');
        Route::resource('users', \App\Http\Controllers\Master\UserController::class);
        
        // Roles & Permissions
        Route::post('roles/bulk-delete', [\App\Http\Controllers\Master\RoleController::class, 'bulkDelete'])->name('roles.bulk-delete')->middleware('can:role.delete');
        Route::resource('roles', \App\Http\Controllers\Master\RoleController::class);
        
        // Transaction Templates (Presets)
        Route::post('templates/bulk-delete', [\App\Http\Controllers\Master\TemplateController::class, 'bulkDelete'])->name('templates.bulk-delete')->middleware('can:template.delete');
        Route::resource('templates', \App\Http\Controllers\Master\TemplateController::class);
    });

    // Pengaturan
    Route::get('/pengaturan', [\App\Http\Controllers\Master\SettingController::class, 'index'])->name('settings.index')->middleware('can:setting.view');
    Route::post('/pengaturan', [\App\Http\Controllers\Master\SettingController::class, 'update'])->name('settings.update')->middleware('can:setting.edit');
    Route::post('/pengaturan/verify-otp', [\App\Http\Controllers\Master\SettingController::class, 'verifyOtp'])->name('settings.verify-otp')->middleware('can:setting.edit');

    // Dedicated Upload API
    Route::post('/api/upload-media', [\App\Http\Controllers\Api\UploadController::class, 'store'])->name('api.upload');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
