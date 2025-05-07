<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Gonon\Digiflazz\Digiflazz;
use App\Http\Controllers\Controller;
use App\Models\Produk;

class KategoriController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'kategori_name', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = Kategori::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kategori_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $categories = $query->paginate(5)->withQueryString();

        // Get static Moogold categories
        $staticCategories = $this->getMoogoldStaticCategories();

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ],
            'staticCategories' => $staticCategories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kategori = $request->validate([
            'kategori_name' => 'required|unique:kategoris,kategori_name|max:50',
            'status' => 'required'
        ]);

        Kategori::create($kategori);

        return to_route('categories.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'New Category has been added!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kategori = Kategori::find($id);
        $kategori->product_count = $kategori->produk()->count();
        return response()->json([
            'category' => $kategori
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // update data berdasarkan id
        $validatedData = $request->validate([
            'kategori_name' => 'required|unique:kategoris,kategori_name|max:50',
            'status' => 'required'
        ]);

        Kategori::where('id', $id)->update($validatedData);

        return to_route('categories.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Category has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // hapus data berdasarkan id
        $kategori = Kategori::find($id);
        $kategori->delete();

        return to_route('categories.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Category has been deleted!']);
    }

    /**
     * Link a category to a Moogold static category.
     */
    public function linkMoogold(Request $request, $id)
    {
        $validatedData = $request->validate([
            'kode_kategori' => 'required|string'
        ]);

        // Verify that the code exists in static categories
        $staticCategories = $this->getMoogoldStaticCategories();
        if (!array_key_exists($validatedData['kode_kategori'], $staticCategories)) {
            return response()->json([
                'message' => 'Invalid Moogold category code.'
            ], 422);
        }

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'kode_kategori' => $validatedData['kode_kategori']
        ]);

        return response()->json([
            'message' => 'Category linked successfully.',
            'category' => $kategori
        ]);
    }

    /**
     * Get available products that can be assigned to a category.
     */
    public function getAvailableProducts(Request $request, $id)
    {
        $search = $request->input('search', '');
        $category = Kategori::findOrFail($id);

        // Get products with pagination
        $query = Produk::where('status', 'active');

        // Apply search if provided
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        // Paginate results
        $products = $query->with('kategori')->paginate(20)->withQueryString();

        return response()->json([
            'products' => $products,
            'category' => $category
        ]);
    }

    /**
     * Bulk assign products to a category.
     */
    public function bulkAssign(Request $request, $id)
    {
        $validatedData = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:produks,id'
        ]);

        $kategori = Kategori::findOrFail($id);

        // Use transaction to ensure data integrity
        \DB::beginTransaction();
        try {
            Produk::whereIn('id', $validatedData['product_ids'])
                ->update(['kategori_id' => $id]);

            \DB::commit();

            return response()->json([
                'message' => count($validatedData['product_ids']) . ' products have been assigned to this category.',
                'category' => $kategori
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'message' => 'Failed to assign products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get static Moogold categories.
     */
    private function getMoogoldStaticCategories()
    {
        return [
            '50' => 'Direct Top-up Products',
            '51' => 'Other Gift Cards',
            '1391' => 'Amazon Gift Cards',
            '1444' => 'Apple Music',
            '766' => 'Garena Shells',
            '538' => 'Google Play',
            '2433' => 'iTunes Gift Card',
            '1223' => 'League of Legends',
            '874' => 'Netflix',
            '765' => 'PSN',
            '451' => 'Razer Gold',
            '1261' => 'Riot Access Code',
            '992' => 'Spotify',
            '993' => 'Steam',
            '2377' => 'Apex Legends',
            '3381' => 'XBox Gift Card',
            '3351' => 'Astro Pay',
            '3075' => 'Bilibili',
            '3382' => 'iQIYI'
        ];
    }
}