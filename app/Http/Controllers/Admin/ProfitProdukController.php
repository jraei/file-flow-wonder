<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Produk;
use App\Models\Layanan;
use App\Models\UserRole;
use App\Models\ProfitProduk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProfitProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $productFilter = $request->input('product');
        $roleFilter = $request->input('role');
        $typeFilter = $request->input('type');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'type', 'value', 'created_at'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = ProfitProduk::with(['produk', 'user_role']);

        // Apply filters
        if ($search) {
            $query->whereHas('produk', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            })->orWhereHas('user_role', function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%");
            });
        }

        if ($productFilter) {
            $query->where('produk_id', $productFilter);
        }

        if ($roleFilter) {
            $query->where('user_roles_id', $roleFilter);
        }

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $profitProduks = $query->paginate(10)->withQueryString();

        // Get active products and user roles for form
        $products = Produk::where('status', 'active')->get();
        $roles = UserRole::all();

        return Inertia::render('Admin/ProfitProduk', [
            'profitProduks' => $profitProduks,
            'products' => $products,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'product' => $productFilter,
                'role' => $roleFilter,
                'type' => $typeFilter
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'user_roles_id' => 'required|exists:user_roles,id',
            'type' => 'required|in:percent,multiplier',
            'value' => 'required|numeric|min:0.01'
        ]);

        // Check if this product+role combination already exists
        $exists = ProfitProduk::where('produk_id', $validated['produk_id'])
            ->where('user_roles_id', $validated['user_roles_id'])
            ->exists();

        if ($exists) {
            return back()->with('status', [
                'type' => 'error',
                'action' => 'Error',
                'text' => 'Profit setting for this product and role already exists!'
            ]);
        }

        ProfitProduk::create($validated);

        return to_route('profit-produks.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'New Profit Setting has been added!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $profitProduk = ProfitProduk::with(['produk', 'user_role'])->find($id);

        return response()->json([
            'profitProduk' => $profitProduk
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'user_roles_id' => 'required|exists:user_roles,id',
            'type' => 'required|in:percent,multiplier',
            'value' => 'required|numeric|min:0.01'
        ]);

        // Check if this product+role combination already exists for other records
        $exists = ProfitProduk::where('produk_id', $validated['produk_id'])
            ->where('user_roles_id', $validated['user_roles_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('status', [
                'type' => 'error',
                'action' => 'Error',
                'text' => 'Profit setting for this product and role already exists!'
            ]);
        }

        $profitProduk = ProfitProduk::findOrFail($id);
        $profitProduk->update($validated);

        return to_route('profit-produks.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Profit Setting has been updated!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $profitProduk = ProfitProduk::findOrFail($id);
        $profitProduk->delete();

        return to_route('profit-produks.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Profit Setting has been deleted!'
        ]);
    }

    /**
     * Bulk create or update profit settings for multiple products
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:produks,id',
            'user_roles_id' => 'required|exists:user_roles,id',
            'type' => 'required|in:percent,multiplier',
            'value' => 'required|numeric|min:0.01'
        ]);

        DB::beginTransaction();
        try {
            $created = 0;
            $updated = 0;

            foreach ($validated['product_ids'] as $productId) {
                // Check if setting exists
                $existingProfit = ProfitProduk::where('produk_id', $productId)
                    ->where('user_roles_id', $validated['user_roles_id'])
                    ->first();

                if ($existingProfit) {
                    // Update existing
                    $existingProfit->update([
                        'type' => $validated['type'],
                        'value' => $validated['value']
                    ]);
                    $updated++;
                } else {
                    // Create new
                    ProfitProduk::create([
                        'produk_id' => $productId,
                        'user_roles_id' => $validated['user_roles_id'],
                        'type' => $validated['type'],
                        'value' => $validated['value']
                    ]);
                    $created++;
                }
            }

            DB::commit();

            $message = "Successfully processed profit settings: {$created} created, {$updated} updated";
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update profit settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview prices for a specific product across all roles.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id'
        ]);

        // Get the product
        $product = Produk::where('id', $validated['produk_id'])
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or inactive'
            ], 404);
        }

        // Get all roles
        $roles = UserRole::all();

        // Get all profit settings for this product
        $profitSettings = ProfitProduk::where('produk_id', $product->id)
            ->with('user_role')
            ->get()
            ->keyBy('user_roles_id');


        // In a real application, you would fetch actual services instead:
        $services = Layanan::where('produk_id', $product->id)
            ->where('status', 'active')
            ->get();

        // Calculate prices for each service and role
        $pricing = [];

        foreach ($services as $service) {
            $priceInfo = [
                'id' => $service['id'],
                'name' => $service['nama_layanan'],
                'code' => $service['kode_layanan'],
                'base_price' => $service['harga_beli'],
                'role_prices' => []
            ];

            foreach ($roles as $role) {
                $finalPrice = $service['harga_beli'];
                $profitType = null;
                $profitValue = null;

                // Check if there's a profit rule for this role and product
                if (isset($profitSettings[$role->id])) {
                    $setting = $profitSettings[$role->id];
                    $profitType = $setting->type;
                    $profitValue = $setting->value;

                    // Calculate price using the profit rule
                    $finalPrice = $setting->calculatePrice($service['harga_beli']);
                }

                $priceInfo['role_prices'][] = [
                    'role_id' => $role->id,
                    'role_name' => $role->display_name,
                    'final_price' => $finalPrice,
                    'profit_type' => $profitType,
                    'profit_value' => $profitValue
                ];
            }

            $pricing[] = $priceInfo;
        }

        // Format thumbnail URL for frontend
        $thumbnailUrl = $product->thumbnail;
        if (!str_starts_with($thumbnailUrl, '/storage/')) {
            $thumbnailUrl = '/storage/' . $thumbnailUrl;
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->nama,
                'thumbnail' => $thumbnailUrl,
                'brand' => $product->brand,
            ],
            'pricing' => $pricing,
        ]);
    }
}
