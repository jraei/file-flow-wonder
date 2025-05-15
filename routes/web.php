<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FlashsaleEventController;
use App\Http\Controllers\Admin\FlashsaleItemController;
use App\Http\Controllers\Admin\ItemThumbnailController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PaymentProviderController;
use App\Http\Controllers\Admin\PayMethodController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\PembelianController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\ProfitProdukController;
use App\Http\Controllers\Admin\ProdukInputFieldController;
use App\Http\Controllers\Admin\ProdukInputOptionController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\TripayCallbackController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\WebConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\XenditController;
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

Route::get('/xendit/get-payment-methods', [XenditController::class, 'getPaymentMethods']);

Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/leaderboard', [IndexController::class, 'leaderboard'])->name('leaderboard');
Route::get('/cek-transaksi', [IndexController::class, 'cekTransaksi'])->name('cek-transaksi');

// Order Processing Routes
Route::get('/order/{produk:slug}', [OrderController::class, 'index'])->name('order.index');
Route::post('/order/confirm', [OrderController::class, 'confirmOrder'])->name('order.confirm');
Route::post('/order/process', [OrderController::class, 'processOrder'])->name('order.process');

// Order Invoice
Route::get('/order/invoice/{order_id}', [OrderController::class, 'invoice'])->name('order.invoice');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard/balance', [DashboardController::class, 'balance'])->name('dashboard.balance');
    Route::get('/dashboard/topup', [DashboardController::class, 'topup'])->name('dashboard.topup');
    Route::post('/dashboard/topup/process', [DashboardController::class, 'processTopup'])->name('dashboard.process.topup');
    Route::get('/dashboard/topup/invoice/{deposit:deposit_id}', [DashboardController::class, 'showInvoice'])->name('invoice.topup');
    Route::get('/dashboard/transactions', [DashboardController::class, 'transactions'])->name('dashboard.transactions');
    Route::get('/dashboard/mutations', [DashboardController::class, 'mutations'])->name('dashboard.mutations');
    Route::get('/dashboard/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');
});

// User Dashboard Routes
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/balance', [DashboardController::class, 'balance'])->name('dashboard.balance');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('dashboard.transactions');
    Route::get('/transactions/export', [DashboardController::class, 'exportTransactions'])->name('dashboard.transactions.export');
    Route::get('/mutations', [DashboardController::class, 'mutations'])->name('dashboard.mutations');
    Route::get('/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');
    Route::get('/topup', [DashboardController::class, 'topup'])->name('dashboard.topup');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/config', [WebConfigController::class, 'index'])->name('web-configs.index');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/userCheck', [UserController::class, 'usernameCheck'])->name('username.check');
    Route::post('/users/topup', [UserController::class, 'topupBalance'])->name('users.topup');
    Route::post('/users/subtract', [UserController::class, 'subtractBalance'])->name('users.subtract');

    // Category Management
    Route::get('/categories', [KategoriController::class, 'index'])->name('categories.index');
    Route::post('/categories', [KategoriController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}', [KategoriController::class, 'show'])->name('categories.show');
    Route::put('/categories/{id}', [KategoriController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [KategoriController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/set-moogold', [KategoriController::class, 'setMoogoldCategories'])->name('categories.setMoogold');

    // Provider Management
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::post('/providers', [ProviderController::class, 'store'])->name('providers.store');
    Route::get('/providers/{id}', [ProviderController::class, 'show'])->name('providers.show');
    Route::put('/providers/{id}', [ProviderController::class, 'update'])->name('providers.update');
    Route::delete('/providers/{id}', [ProviderController::class, 'destroy'])->name('providers.destroy');

    // Payment Provider Management
    Route::get('/payment-providers', [PaymentProviderController::class, 'index'])->name('payment-providers.index');
    Route::post('/payment-providers', [PaymentProviderController::class, 'store'])->name('payment-providers.store');
    Route::get('/payment-providers/{id}', [PaymentProviderController::class, 'show'])->name('payment-providers.show');
    Route::put('/payment-providers/{id}', [PaymentProviderController::class, 'update'])->name('payment-providers.update');
    Route::delete('/payment-providers/{id}', [PaymentProviderController::class, 'destroy'])->name('payment-providers.destroy');

    // Payment Method Management
    Route::get('/pay-methods', [PayMethodController::class, 'index'])->name('pay-methods.index');
    Route::post('/pay-methods', [PayMethodController::class, 'store'])->name('pay-methods.store');
    Route::get('/pay-methods/{id}', [PayMethodController::class, 'show'])->name('pay-methods.show');
    Route::put('/pay-methods/{id}', [PayMethodController::class, 'update'])->name('pay-methods.update');
    Route::delete('/pay-methods/{id}', [PayMethodController::class, 'destroy'])->name('pay-methods.destroy');

    // Banner Management
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::get('/banners/{id}', [BannerController::class, 'show'])->name('banners.show');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

    // Flashsale Event Management
    Route::get('/flashsale-events', [FlashsaleEventController::class, 'index'])->name('flashsale-events.index');
    Route::post('/flashsale-events', [FlashsaleEventController::class, 'store'])->name('flashsale-events.store');
    Route::get('/flashsale-events/{id}', [FlashsaleEventController::class, 'show'])->name('flashsale-events.show');
    Route::put('/flashsale-events/{id}', [FlashsaleEventController::class, 'update'])->name('flashsale-events.update');
    Route::delete('/flashsale-events/{id}', [FlashsaleEventController::class, 'destroy'])->name('flashsale-events.destroy');

    // Flashsale Item Management
    Route::get('/flashsale-items', [FlashsaleItemController::class, 'index'])->name('flashsale-items.index');
    Route::post('/flashsale-items', [FlashsaleItemController::class, 'store'])->name('flashsale-items.store');
    Route::get('/flashsale-items/{id}', [FlashsaleItemController::class, 'show'])->name('flashsale-items.show');
    Route::put('/flashsale-items/{id}', [FlashsaleItemController::class, 'update'])->name('flashsale-items.update');
    Route::delete('/flashsale-items/{id}', [FlashsaleItemController::class, 'destroy'])->name('flashsale-items.destroy');

    // Product Management
    Route::get('/products', [ProdukController::class, 'index'])->name('products.index');
    Route::post('/products', [ProdukController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProdukController::class, 'show'])->name('products.show');
    Route::put('/products/{id}', [ProdukController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProdukController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/getByProvider/{provider}', [ProdukController::class, 'getProductsByProvider'])->name('products.getProductsByProvider');
    Route::post('/products/{id}/validation', [ProdukController::class, 'updateValidation'])->name('products.updateValidation');

    // Item Thumbnail Management
    Route::get('/item-thumbnails', [ItemThumbnailController::class, 'index'])->name('item-thumbnails.index');
    Route::post('/item-thumbnails', [ItemThumbnailController::class, 'store'])->name('item-thumbnails.store');
    Route::get('/item-thumbnails/{id}', [ItemThumbnailController::class, 'show'])->name('item-thumbnails.show');
    Route::put('/item-thumbnails/{id}', [ItemThumbnailController::class, 'update'])->name('item-thumbnails.update');
    Route::delete('/item-thumbnails/{id}', [ItemThumbnailController::class, 'destroy'])->name('item-thumbnails.destroy');

    // Profit Produk Management
    Route::get('/profit-produks', [ProfitProdukController::class, 'index'])->name('profit-produks.index');
    Route::post('/profit-produks', [ProfitProdukController::class, 'store'])->name('profit-produks.store');
    Route::get('/profit-produks/{id}', [ProfitProdukController::class, 'show'])->name('profit-produks.show');
    Route::put('/profit-produks/{id}', [ProfitProdukController::class, 'update'])->name('profit-produks.update');
    Route::delete('/profit-produks/{id}', [ProfitProdukController::class, 'destroy'])->name('profit-produks.destroy');
    Route::post('/profit-produks/preview', [ProfitProdukController::class, 'preview'])->name('profit-produks.preview');
    Route::post('/profit-produks/bulk-update', [ProfitProdukController::class, 'bulkUpdate'])->name('profit-produks.bulkUpdate');

    // Product Input Field Management
    Route::get('/produk-input-fields', [ProdukInputFieldController::class, 'index'])->name('produk-input-fields.index');
    Route::post('/produk-input-fields', [ProdukInputFieldController::class, 'store'])->name('produk-input-fields.store');
    Route::get('/produk-input-fields/{id}', [ProdukInputFieldController::class, 'show'])->name('produk-input-fields.show');
    Route::put('/produk-input-fields/{id}', [ProdukInputFieldController::class, 'update'])->name('produk-input-fields.update');
    Route::delete('/produk-input-fields/{id}', [ProdukInputFieldController::class, 'destroy'])->name('produk-input-fields.destroy');

    // Product Input Option Management
    Route::get('/produk-input-options', [ProdukInputOptionController::class, 'index'])->name('produk-input-options.index');
    Route::post('/produk-input-options', [ProdukInputOptionController::class, 'store'])->name('produk-input-options.store');
    Route::get('/produk-input-options/{id}', [ProdukInputOptionController::class, 'show'])->name('produk-input-options.show');
    Route::put('/produk-input-options/{id}', [ProdukInputOptionController::class, 'update'])->name('produk-input-options.update');
    Route::delete('/produk-input-options/{id}', [ProdukInputOptionController::class, 'destroy'])->name('produk-input-options.destroy');

    // Service Management
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
    Route::get('/layanan/{id}', [LayananController::class, 'show'])->name('layanan.show');
    Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');
    Route::delete('/layanan', [LayananController::class, 'deleteLayanan'])->name('services.deleteLayanan');
    Route::post('/layanan/updateLayanan', [LayananController::class, 'updateLayanan'])->name('layanan.updateLayanan');

    // Voucher Management
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{id}', [VoucherController::class, 'show'])->name('vouchers.show');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // Transaction Management
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/{id}', [PembelianController::class, 'show'])->name('pembelian.show');
    Route::put('/pembelian/{id}', [PembelianController::class, 'update'])->name('pembelian.update');

    // Deposit Management
    Route::get('/deposits', [PembayaranController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/{id}', [PembayaranController::class, 'show'])->name('deposits.show');
    Route::put('/deposits/{id}', [PembayaranController::class, 'update'])->name('deposits.update');
});

// Tripay Callback
Route::post('/callback', [TripayCallbackController::class, 'handle']);

require __DIR__ . '/auth.php';
