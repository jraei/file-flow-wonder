<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Gonon\Digiflazz\Digiflazz;
use App\Http\Controllers\Controller;


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

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
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

        // return to_route('categories.index')->with('success', 'New Category has been added!');
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
        // dd($kategori->id);
        $kategori = Kategori::find($id);
        $kategori->delete();

        return to_route('categories.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Category has been deleted!']);
    }
}
