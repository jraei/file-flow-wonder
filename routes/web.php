
<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlashsaleEventController;
use App\Http\Controllers\Admin\FlashsaleItemController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PayMethodController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\PembelianController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\WebConfigController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\DashboardController as UserDashboardController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MoogoldController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('order/{produk:id}', [OrderController::class, 'index'])->name('order.index');
Route::post('order/submit', [OrderController::class, 'submit'])->name('order.submit');
Route::post('order/check-auth', [OrderController::class, 'checkAuth'])->name('order.auth');
Route::get('order/invoice/{reference_id}', [OrderController::class, 'invoice'])->name('order.invoice');
Route::post('order/fetch-layanan', [AjaxController::class, 'fetchLayanan'])->name('order.fetchLayanan');
Route::get('order/fetch-payment-methods', [AjaxController::class, 'fetchPaymentMethods'])->name('order.fetchPaymentMethods');
Route::post('order/check-voucher', [AjaxController::class, 'checkVoucher'])->name('order.checkVoucher');

// Cek Transaksi
Route::get('/cek-transaksi', [MoogoldController::class, 'search'])->name('cek.transaksi');
Route::get('/cek-transaksi/{reference_id}', [MoogoldController::class, 'showTransaction'])->name('transaction.detail');

// Ajax Routes
Route::post('/fetch-servers', [AjaxController::class, 'fetchServers'])->name('ajax.fetchServers');
Route::post('/fetch-data-pengguna', [AjaxController::class, 'fetchDataPengguna'])->name('ajax.fetchDataPengguna');
Route::post('/fetch-info-akun', [AjaxController::class, 'fetchInfoAkun'])->name('ajax.fetchInfoAkun');

// Dashboard for authenticated users
Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/balance', [UserDashboardController::class, 'balance'])->name('dashboard.balance');
    Route::get('/topup', [UserDashboardController::class, 'topup'])->name('dashboard.topup');
    Route::get('/transactions', [UserDashboardController::class, 'transactions'])->name('dashboard.transactions');
    Route::get('/mutations', [UserDashboardController::class, 'mutations'])->name('dashboard.mutations');
    Route::get('/affiliate', [UserDashboardController::class, 'affiliate'])->name('dashboard.affiliate');
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/products', [DashboardController::class, 'products'])->name('admin.dashboard.products');
    Route::get('/dashboard/product-services/{productId?}', [DashboardController::class, 'productServices'])->name('admin.dashboard.productServices');
    Route::get('/dashboard/flashsales', [DashboardController::class, 'flashsales'])->name('admin.dashboard.flashsales');
    Route::get('/dashboard/vouchers', [DashboardController::class, 'vouchers'])->name('admin.dashboard.vouchers');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('admin.dashboard.export');

    // Categories
    Route::resource('categories', KategoriController::class);
    
    // Products
    Route::resource('products', ProdukController::class);
    
    // Layanan (Services)
    Route::resource('services', LayananController::class);
    
    // Users
    Route::resource('users', UserController::class);
    
    // Pembelians (Purchases)
    Route::resource('pembelians', PembelianController::class);
    Route::post('pembelians/status', [PembelianController::class, 'updateStatus'])->name('pembelians.updateStatus');
    
    // Pembayarans (Payments)
    Route::resource('pembayarans', PembayaranController::class);

    // Flashsale Events
    Route::resource('flashsale-events', FlashsaleEventController::class);
    Route::post('flashsale-events/status', [FlashsaleEventController::class, 'updateStatus'])->name('flashsale-events.updateStatus');

    // Flashsale Items
    Route::resource('flashsale-items', FlashsaleItemController::class);
    Route::get('flashsale-items/by-event/{eventId}', [FlashsaleItemController::class, 'byEvent'])->name('flashsale-items.byEvent');

    // Pay Methods
    Route::resource('paymethods', PayMethodController::class);
    Route::post('paymethods/status', [PayMethodController::class, 'updateStatus'])->name('paymethods.updateStatus');

    // Banners
    Route::resource('banners', BannerController::class);
    Route::post('banners/status', [BannerController::class, 'updateStatus'])->name('banners.updateStatus');

    // Vouchers
    Route::resource('vouchers', VoucherController::class);
    Route::post('vouchers/status', [VoucherController::class, 'updateStatus'])->name('vouchers.updateStatus');
    
    // WebConfigs
    Route::resource('web-configs', WebConfigController::class);
    Route::post('web-configs/update-multi', [WebConfigController::class, 'updateMultiple'])->name('web-configs.updateMultiple');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Leaderboard
Route::get('/leaderboard', function () {
    return Inertia::render('Leaderboard');
})->name('leaderboard');
