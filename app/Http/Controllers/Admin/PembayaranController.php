<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Pembayaran::with(['pembelian'])->paginate(10);
        return response()->json($payments);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembayaran $pembayaran)
    {
        return response()->json([
            'payment' => $pembayaran->load(['pembelian'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
