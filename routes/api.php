<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Produk;
use App\Http\Controllers\Admin\CheckUsernameController;

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

// Account validation endpoint
Route::post('/validate-account', function (Request $request) {
    // Validate request
    $request->validate([
        'produk_slug' => 'required|string|exists:produks,slug',
        'inputs' => 'required|array',
        'validasi_id' => 'required|string',
    ]);

    // Get product by slug
    $produk = Produk::where('slug', $request->produk_slug)->first();

    if (!$produk) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }

    // Check if validation is required
    if ($produk->validasi_id === 'tidak') {
        return response()->json([
            'status' => 'success',
            'username' => null,
            'message' => 'No validation required for this product'
        ]);
    }

    // Extract user ID and zone/server ID
    $userId = null;
    $zoneId = null;

    // Get the input fields for this product
    $inputFields = $produk->inputFields()->with('options')->get();

    // Find the user ID and zone/server fields from inputs
    foreach ($inputFields as $field) {
        $fieldName = $field->name;

        if (isset($request->inputs[$fieldName])) {
            if ($field->isUserIdField()) {
                $userId = $request->inputs[$fieldName];
            } elseif ($field->isServerField()) {
                $zoneId = $request->inputs[$fieldName];
            }
        }
    }

    // Check if we have the required fields for validation
    if (!$userId) {
        return response()->json([
            'status' => 'error',
            'message' => 'User ID is required for account validation'
        ], 400);
    }

    // Prepare validation data
    $validationData = [
        'game' => $produk->validasi_id,
        'user_id' => $userId
    ];

    if ($zoneId) {
        $validationData['zone_id'] = $zoneId;
    }

    // Call username validation service
    $controller = new CheckUsernameController();
    $response = $controller->getAccountUsername($validationData);

    // Convert Laravel response to array for the frontend
    $responseContent = json_decode($response->getContent(), true);
    $statusCode = $response->getStatusCode();

    return response()->json($responseContent, $statusCode);
})->name('api.validate.account');