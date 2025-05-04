<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $filter = $request->input('filter');

        // Validate sort field
        $allowedSortFields = ['id', 'order_id', 'price', 'profit', 'status', 'created_at'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query with eager loading
        $query = Pembelian::with(['user', 'layanan', 'pembayaran']);

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('input_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('layanan', function ($layananQuery) use ($search) {
                        $layananQuery->where('nama_layanan', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status if provided
        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'processing') {
            $query->where('status', 'processing');
        } elseif ($filter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($filter === 'failed') {
            $query->where('status', 'failed');
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $pembelians = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Pembelians', [
            'pembelians' => $pembelians,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'filter' => $filter
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pembelian = Pembelian::with(['user', 'layanan', 'pembayaran'])->find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        return response()->json([
            'pembelian' => $pembelian
        ]);
    }

    /**
     * Process the specified purchase.
     */
    public function process(Request $request, $id)
    {
        $pembelian = Pembelian::find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Validate that the purchase is in pending status
        if ($pembelian->status !== 'pending') {
            return response()->json(['error' => 'Only pending purchases can be processed'], 422);
        }

        // Update to success status
        $pembelian->status = 'completed';
        $pembelian->save();

        return response()->json([
            'message' => 'Purchase processed successfully',
            'pembelian' => $pembelian->load(['user', 'layanan', 'pembayaran'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        $pembelian->delete();

        return to_route('pembelians.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Purchase has been deleted!'
        ]);
    }
}