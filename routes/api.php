
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Produk;

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
                      ->get(['id', 'nama', 'thumbnail']);
    
    return response()->json($products);
})->name('api.search.products');
