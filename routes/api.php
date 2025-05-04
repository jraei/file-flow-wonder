
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Payment QR Code endpoint
Route::get('/payments/{reference}/qr-code', function ($reference) {
    $payment = \App\Models\Pembayaran::where('payment_reference', $reference)->first();
    
    if (!$payment || !$payment->qr_code_path) {
        return response()->json(['error' => 'QR code not found'], 404);
    }
    
    // Check if it's a URL
    if (filter_var($payment->qr_code_path, FILTER_VALIDATE_URL)) {
        return redirect($payment->qr_code_path);
    }
    
    // Check if it's a local file
    if (file_exists(storage_path('app/public/' . $payment->qr_code_path))) {
        return response()->file(storage_path('app/public/' . $payment->qr_code_path));
    }
    
    return response()->json(['error' => 'QR code not accessible'], 404);
});
