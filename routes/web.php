<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/export', [App\Http\Controllers\Admin\AdminController::class, 'exportDashboard'])->name('admin.dashboard.export');
    
    Route::get('/admin/settings', [App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/general', [App\Http\Controllers\Admin\AdminController::class, 'updateGeneralSettings'])->name('admin.settings.general');
    Route::post('/admin/settings/appearance', [App\Http\Controllers\Admin\AdminController::class, 'updateAppearance'])->name('admin.settings.appearance');
    Route::post('/admin/settings/api', [App\Http\Controllers\Admin\AdminController::class, 'updateApiConnections'])->name('admin.settings.api');
    Route::delete('/admin/settings/logo/{field}', [App\Http\Controllers\Admin\AdminController::class, 'deleteLogo'])->name('admin.settings.logo.delete');
    
    Route::get('/admin/categories', [App\Http\Controllers\Admin\AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/banners', [App\Http\Controllers\Admin\AdminController::class, 'banners'])->name('admin.banners');
    
    Route::resource('pembelians', App\Http\Controllers\Admin\PembelianController::class);
    Route::post('/pembelians/{id}/process', [App\Http\Controllers\Admin\PembelianController::class, 'process'])->name('pembelians.process');
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/balance', [App\Http\Controllers\DashboardController::class, 'balance'])->name('dashboard.balance');
    Route::get('/dashboard/topup', [App\Http\Controllers\DashboardController::class, 'topup'])->name('dashboard.topup');
    Route::get('/dashboard/transactions', [App\Http\Controllers\DashboardController::class, 'transactions'])->name('dashboard.transactions');
    Route::get('/dashboard/mutations', [App\Http\Controllers\DashboardController::class, 'mutations'])->name('dashboard.mutations');
    Route::get('/dashboard/affiliate', [App\Http\Controllers\DashboardController::class, 'affiliate'])->name('dashboard.affiliate');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
