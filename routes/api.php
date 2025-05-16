
<?php

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CheckUsernameController;
use App\Http\Controllers\Admin\TripayCallbackController;
use App\Http\Controllers\CalculatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Product search endpoint
Route::get('/search/products', function (Request $request) {
    $query = $request->input('query');

    if (empty($query) || strlen($query) < 2) {
        return response()->json([]);
    }

    $products = Produk::where('nama', 'LIKE', "%{$query}%")
        ->where('status', 'active')
        ->take(5)
        ->get(['id', 'nama', 'thumbnail', 'slug']);

    return response()->json($products);
})->name('api.search.products');

// Calculator endpoints
Route::post('/calculator/winrate', [CalculatorController::class, 'calculateWinrate']);

// payment gateway callback handle
Route::prefix('callback')->group(function () {
    Route::post('/tripay', [TripayCallbackController::class, 'handle']);
});


// We no longer need a separate validate-account endpoint as validation
// is now integrated with the order/confirm endpoint

