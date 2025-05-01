<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\ProdukInputField;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProdukInputFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'order');
        $direction = $request->input('direction', 'asc');
        $productId = $request->input('product_id');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'name', 'label', 'type', 'required', 'order', 'produk_id'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'order';
        }

        // Start query with eager loading
        $query = ProdukInputField::with('produk');

        // Filter by product if specified
        if ($productId) {
            $query->where('produk_id', $productId);
        }

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $fields = $query->paginate(10)->withQueryString();

        // Get all products for dropdown
        $products = Produk::select('id', 'nama')->get();

        return Inertia::render('Admin/ProdukInputFields', [
            'fields' => $fields,
            'products' => $products,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'product_id' => $productId
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|exists:produks,id',
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request) {
                    // Check for duplicate field name per product
                    $exists = ProdukInputField::where('produk_id', $request->produk_id)
                        ->where('name', $value)
                        ->exists();

                    if ($exists) {
                        $fail('The field name already exists for this product.');
                    }
                },
            ],
            'label' => 'required|string|max:100',
            'type' => 'required|in:text,number,select',
            'required' => 'required|boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Set order if not provided
        if (!$request->has('order')) {
            $maxOrder = ProdukInputField::where('produk_id', $request->produk_id)
                ->max('order');
            $request->merge(['order' => ($maxOrder ?? 0) + 10]);
        }

        // Create the input field
        $field = ProdukInputField::create($request->all());

        return redirect()->route('admin.produk-input-fields.index')
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Input field has been added!']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $field = ProdukInputField::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|exists:produks,id',
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request, $field) {
                    // Check for duplicate field name per product (excluding current field)
                    $exists = ProdukInputField::where('produk_id', $request->produk_id)
                        ->where('name', $value)
                        ->where('id', '!=', $field->id)
                        ->exists();

                    if ($exists) {
                        $fail('The field name already exists for this product.');
                    }
                },
            ],
            'label' => 'required|string|max:100',
            'type' => 'required|in:text,number,select',
            'required' => 'required|boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if field type has changed from select to something else
        if ($field->type === 'select' && $request->type !== 'select') {
            // Delete associated options when type is no longer select
            $field->options()->delete();
        }

        $field->update($request->all());

        return redirect()->route('admin.produk-input-fields.index')
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Input field has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $field = ProdukInputField::findOrFail($id);

        // Options will cascade delete due to foreign key constraint
        $field->delete();

        return redirect()->route('admin.produk-input-fields.index')
            ->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Input field has been deleted!']);
    }

    /**
     * Update the order of multiple fields at once.
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:produk_input_fields,id',
            'fields.*.order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->fields as $field) {
            ProdukInputField::where('id', $field['id'])->update(['order' => $field['order']]);
        }

        return response()->json(['message' => 'Field order updated successfully']);
    }
}
