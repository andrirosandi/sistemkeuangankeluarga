<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
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

    // Kas Masuk & Kas Keluar (register once via loop)
    foreach (['in' => 'kas-masuk', 'out' => 'kas-keluar'] as $type => $prefix) {
        Route::prefix($prefix)->name("{$type}.")->group(function () use ($type) {
            $requestCtrl = \App\Http\Controllers\Transaction\RequestController::class;
            $trxCtrl = \App\Http\Controllers\Transaction\TransactionController::class;

            // Pengajuan
            Route::get('/pengajuan', [$requestCtrl, 'index'])->name('request.index')->defaults('type', $type)->middleware("can:{$type}.request.view");
            Route::get('/pengajuan/create', [$requestCtrl, 'create'])->name('request.create')->defaults('type', $type)->middleware("can:{$type}.request.create");
            Route::post('/pengajuan', [$requestCtrl, 'store'])->name('request.store')->defaults('type', $type)->middleware("can:{$type}.request.create");
            Route::get('/pengajuan/{id}', [$requestCtrl, 'show'])->name('request.show')->defaults('type', $type)->middleware("can:{$type}.request.view");
            Route::get('/pengajuan/{id}/edit', [$requestCtrl, 'edit'])->name('request.edit')->defaults('type', $type)->middleware("can:{$type}.request.edit");
            Route::put('/pengajuan/{id}', [$requestCtrl, 'update'])->name('request.update')->defaults('type', $type)->middleware("can:{$type}.request.edit");
            Route::delete('/pengajuan/{id}', [$requestCtrl, 'destroy'])->name('request.destroy')->defaults('type', $type)->middleware("can:{$type}.request.delete");
            Route::post('/pengajuan/{id}/submit', [$requestCtrl, 'submit'])->name('request.submit')->defaults('type', $type)->middleware("can:{$type}.request.edit");
            Route::post('/pengajuan/{id}/approve', [$requestCtrl, 'approve'])->name('request.approve')->defaults('type', $type)->middleware("can:{$type}.request.approve");
            Route::post('/pengajuan/{id}/reject', [$requestCtrl, 'reject'])->name('request.reject')->defaults('type', $type)->middleware("can:{$type}.request.approve");

            // Realisasi
            Route::get('/realisasi', [$trxCtrl, 'index'])->name('transaction.index')->defaults('type', $type)->middleware("can:{$type}.transaction.view");
            Route::get('/realisasi/create', [$trxCtrl, 'create'])->name('transaction.create')->defaults('type', $type)->middleware("can:{$type}.transaction.create");
            Route::post('/realisasi', [$trxCtrl, 'store'])->name('transaction.store')->defaults('type', $type)->middleware("can:{$type}.transaction.create");
            Route::get('/realisasi/{id}', [$trxCtrl, 'show'])->name('transaction.show')->defaults('type', $type)->middleware("can:{$type}.transaction.view");
            Route::get('/realisasi/{id}/edit', [$trxCtrl, 'edit'])->name('transaction.edit')->defaults('type', $type)->middleware("can:{$type}.transaction.edit");
            Route::put('/realisasi/{id}', [$trxCtrl, 'update'])->name('transaction.update')->defaults('type', $type)->middleware("can:{$type}.transaction.edit");
            Route::post('/realisasi/{id}/complete', [$trxCtrl, 'complete'])->name('transaction.complete')->defaults('type', $type)->middleware("can:{$type}.transaction.edit");
            Route::post('/realisasi/{id}/cancel', [$trxCtrl, 'cancel'])->name('transaction.cancel')->defaults('type', $type)->middleware("can:{$type}.transaction.edit");
            Route::delete('/realisasi/{id}', [$trxCtrl, 'destroy'])->name('transaction.destroy')->defaults('type', $type)->middleware("can:{$type}.transaction.delete");
        });
    }

    // Mutasi
    Route::prefix('mutasi')->name('mutation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Transaction\MutationController::class, 'index'])->name('index')->middleware('can:mutation.view');
        Route::get('/print', [\App\Http\Controllers\Transaction\MutationController::class, 'index'])->name('print')->middleware('can:mutation.view');
    });

    // Laporan & Analitik
    Route::prefix('laporan')->name('report.')->middleware('can:report.view.self')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/tahunan', [ReportController::class, 'annual'])->name('annual');
        Route::get('/kategori', [ReportController::class, 'category'])->name('category');
        Route::get('/mutasi', [ReportController::class, 'mutation'])->name('mutation');
        Route::get('/efisiensi', [ReportController::class, 'efficiency'])->name('efficiency');
        Route::get('/outstanding', [ReportController::class, 'outstanding'])->name('outstanding');
        Route::get('/per-anggota', [ReportController::class, 'perMember'])->name('per-member')->middleware('can:report.view');
        Route::get('/pemasukan', [ReportController::class, 'income'])->name('income');
        Route::get('/mutasi/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf')->middleware('can:report.export');
        Route::get('/mutasi/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel')->middleware('can:report.export');
    });

    // Master Data
    // Notifikasi
    Route::prefix('notifikasi')->name('notification.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('readAll');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

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

    // Dashboard Widget API
    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/balance', [DashboardController::class, 'widgetBalance'])->name('balance')->middleware('can:dashboard.system.balance');
        Route::get('/summary', [DashboardController::class, 'widgetSummary'])->name('summary')->middleware('can:dashboard.widget.summary');
        Route::get('/activity', [DashboardController::class, 'widgetActivity'])->name('activity')->middleware('can:dashboard.widget.activity');
        Route::get('/alerts', [DashboardController::class, 'widgetAlerts'])->name('alerts')->middleware('can:dashboard.widget.alerts');
        Route::get('/recent', [DashboardController::class, 'widgetRecent'])->name('recent')->middleware('can:dashboard.widget.recent');
        Route::get('/request-summary', [DashboardController::class, 'widgetRequestSummary'])->name('request-summary')->middleware('can:dashboard.widget.request-summary');
        Route::get('/category-breakdown', [DashboardController::class, 'widgetCategoryBreakdown'])->name('category-breakdown')->middleware('can:dashboard.widget.category');
        Route::get('/group-ranking', [DashboardController::class, 'widgetGroupRanking'])->name('group-ranking')->middleware('can:dashboard.widget.group-ranking');
        Route::get('/user-ranking', [DashboardController::class, 'widgetUserRanking'])->name('user-ranking')->middleware('can:dashboard.widget.user-ranking');
        Route::get('/outstanding', [DashboardController::class, 'widgetOutstanding'])->name('outstanding')->middleware('can:dashboard.widget.outstanding');
        Route::get('/month-compare', [DashboardController::class, 'widgetMonthCompare'])->name('month-compare')->middleware('can:dashboard.widget.month-compare');
        Route::get('/approval-stats', [DashboardController::class, 'widgetApprovalStats'])->name('approval-stats')->middleware('can:dashboard.widget.approval-stats');
    });
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
