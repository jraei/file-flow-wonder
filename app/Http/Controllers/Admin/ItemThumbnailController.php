<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\ItemThumbnail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemThumbnailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $productId = $request->input('product_id');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'produk_id', 'min_item', 'max_item', 'is_static', 'default_for_produk'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = ItemThumbnail::with('produk');

        // Apply search if provided
        if ($search) {
            $query->whereHas('produk', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by product if provided
        if ($productId) {
            $query->where('produk_id', $productId);
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $thumbnails = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/ItemThumbnails', [
            'thumbnails' => $thumbnails,
            'products' => Produk::where('status', 'active')->get(),
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
        $data = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'min_item' => 'nullable|integer|min:0',
            'max_item' => 'nullable|integer|min:0',
            'is_static' => 'boolean',
            'default_for_produk' => 'boolean',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // Validate ranges based on static flag
        if ($data['is_static']) {
            $data['min_item'] = null;
            $data['max_item'] = null;
        } else {
            // Check if min is less than max
            if ($data['min_item'] >= $data['max_item']) {
                throw ValidationException::withMessages([
                    'min_item' => 'Minimum item must be less than maximum item'
                ]);
            }

            // Check for range conflicts
            $conflict = ItemThumbnail::where('produk_id', $data['produk_id'])
                ->where('is_static', false)
                ->where(function ($query) use ($data) {
                    $query->whereBetween('min_item', [$data['min_item'], $data['max_item']])
                        ->orWhereBetween('max_item', [$data['min_item'], $data['max_item']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('min_item', '<=', $data['min_item'])
                                ->where('max_item', '>=', $data['max_item']);
                        });
                })->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'min_item' => 'Range conflicts with an existing thumbnail rule'
                ]);
            }
        }

        // Handle default flag
        if ($data['default_for_produk']) {
            // Remove default flag from other thumbnails for same product
            ItemThumbnail::where('produk_id', $data['produk_id'])
                ->where('default_for_produk', true)
                ->update(['default_for_produk' => false]);
        }

        // Handle file upload
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('thumbnails', 'public');
        }

        ItemThumbnail::create($data);

        return to_route('item-thumbnails.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'New thumbnail rule has been added!'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemThumbnail $itemThumbnail)
    {
        // dd('tes');
        $data = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'min_item' => 'nullable|integer|min:0',
            'max_item' => 'nullable|integer|min:0',
            'is_static' => 'boolean',
            'default_for_produk' => 'boolean',
        ]);

        // Validate ranges based on static flag
        if ($data['is_static']) {
            $data['min_item'] = null;
            $data['max_item'] = null;
        } else {
            // Check if min is less than max
            if ($data['min_item'] >= $data['max_item']) {
                throw ValidationException::withMessages([
                    'min_item' => 'Minimum item must be less than maximum item'
                ]);
            }

            // Check for range conflicts with other thumbnails
            $conflict = ItemThumbnail::where('produk_id', $data['produk_id'])
                ->where('id', '!=', $itemThumbnail->id)
                ->where('is_static', false)
                ->where(function ($query) use ($data) {
                    $query->whereBetween('min_item', [$data['min_item'], $data['max_item']])
                        ->orWhereBetween('max_item', [$data['min_item'], $data['max_item']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('min_item', '<=', $data['min_item'])
                                ->where('max_item', '>=', $data['max_item']);
                        });
                })->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'min_item' => 'Range conflicts with an existing thumbnail rule'
                ]);
            }
        }

        // Handle default flag
        if ($data['default_for_produk'] && !$itemThumbnail->default_for_produk) {
            // Remove default flag from other thumbnails for same product
            ItemThumbnail::where('produk_id', $data['produk_id'])
                ->where('id', '!=', $itemThumbnail->id)
                ->where('default_for_produk', true)
                ->update(['default_for_produk' => false]);
        }

        // Handle file upload
        if ($request->hasFile('image_path')) {
            // Delete old image
            if ($itemThumbnail->image_path) {
                Storage::disk('public')->delete($itemThumbnail->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('thumbnails', 'public');
        }

        // Update the thumbnail
        $itemThumbnail->update($data);

        return to_route('item-thumbnails.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Thumbnail rule has been updated!'
        ]);
    }

    /**
     * Toggle static flag for a thumbnail.
     */
    public function toggleStatic(ItemThumbnail $itemThumbnail)
    {
        $itemThumbnail->update([
            'is_static' => !$itemThumbnail->is_static,
            // Clear min/max if switching to static
            'min_item' => $itemThumbnail->is_static ? null : $itemThumbnail->min_item,
            'max_item' => $itemThumbnail->is_static ? null : $itemThumbnail->max_item,
        ]);

        return to_route('item-thumbnails.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Static status updated successfully!'
        ]);
    }

    /**
     * Toggle default flag for a thumbnail.
     */
    public function toggleDefault(ItemThumbnail $itemThumbnail)
    {
        // If turning on default
        if (!$itemThumbnail->default_for_produk) {
            // Remove default flag from other thumbnails for this product
            ItemThumbnail::where('produk_id', $itemThumbnail->produk_id)
                ->where('id', '!=', $itemThumbnail->id)
                ->where('default_for_produk', true)
                ->update(['default_for_produk' => false]);
        } else {
            // Check if there are other thumbnails for this product
            $otherThumbnailsExist = ItemThumbnail::where('produk_id', $itemThumbnail->produk_id)
                ->where('id', '!=', $itemThumbnail->id)
                ->exists();

            // Don't allow turning off default if this is the only thumbnail
            if (!$otherThumbnailsExist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove default status: at least one default thumbnail is required per product'
                ], 422);
            }
        }

        $itemThumbnail->update([
            'default_for_produk' => !$itemThumbnail->default_for_produk
        ]);

        return to_route('item-thumbnails.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Default status updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemThumbnail $itemThumbnail)
    {
        // If this is the default thumbnail and the only one for this product, prevent deletion
        if ($itemThumbnail->default_for_produk) {
            $thumbnailCount = ItemThumbnail::where('produk_id', $itemThumbnail->produk_id)->count();
            if ($thumbnailCount <= 1) {
                return to_route('item-thumbnails.index')->with('status', [
                    'type' => 'error',
                    'action' => 'Error',
                    'text' => 'Cannot delete the only default thumbnail for this product!'
                ]);
            }
        }

        // Delete the image file
        if ($itemThumbnail->image_path) {
            Storage::disk('public')->delete($itemThumbnail->image_path);
        }

        $itemThumbnail->delete();

        return to_route('item-thumbnails.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Thumbnail rule has been deleted!'
        ]);
    }

    /**
     * Bulk assign thumbnail to multiple products.
     */
    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'produk_ids' => 'required|array',
            'produk_ids.*' => 'exists:produks,id',
            'min_item' => 'nullable|integer|min:0',
            'max_item' => 'nullable|integer|min:0',
            'is_static' => 'boolean',
            'default_for_produk' => 'boolean',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // Store the image once
        $imagePath = $request->file('image_path')->store('thumbnails', 'public');

        $successCount = 0;
        $failCount = 0;

        foreach ($data['produk_ids'] as $produkId) {
            try {
                // If setting as default, remove default flag from other thumbnails
                if ($data['default_for_produk']) {
                    ItemThumbnail::where('produk_id', $produkId)
                        ->where('default_for_produk', true)
                        ->update(['default_for_produk' => false]);
                }

                // Check for range conflicts for this product if not static
                if (!$data['is_static']) {
                    $conflict = ItemThumbnail::where('produk_id', $produkId)
                        ->where('is_static', false)
                        ->where(function ($query) use ($data) {
                            $query->whereBetween('min_item', [$data['min_item'], $data['max_item']])
                                ->orWhereBetween('max_item', [$data['min_item'], $data['max_item']])
                                ->orWhere(function ($q) use ($data) {
                                    $q->where('min_item', '<=', $data['min_item'])
                                        ->where('max_item', '>=', $data['max_item']);
                                });
                        })->exists();

                    if ($conflict) {
                        $failCount++;
                        continue; // Skip this product
                    }
                }

                // Create the thumbnail
                ItemThumbnail::create([
                    'produk_id' => $produkId,
                    'min_item' => $data['is_static'] ? null : $data['min_item'],
                    'max_item' => $data['is_static'] ? null : $data['max_item'],
                    'is_static' => $data['is_static'],
                    'default_for_produk' => $data['default_for_produk'],
                    'image_path' => $imagePath,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $message = "{$successCount} thumbnails were created successfully.";
        if ($failCount > 0) {
            $message .= " {$failCount} failed due to conflicts.";
        }

        return to_route('item-thumbnails.index')->with('status', [
            'type' => $failCount > 0 ? 'warning' : 'success',
            'action' => $failCount > 0 ? 'Partial Success' : 'Success',
            'text' => $message
        ]);
    }
}
