<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'image_path', 'order', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = Banner::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('image_path', 'like', "%{$search}%")
                    ->orWhere('order', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $banners = $query->paginate(5)->withQueryString();

        return Inertia::render('Admin/Banners', [
            'banners' => $banners,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
        ]);
    }

    public function store(Request $request)
    {
        $banner = $request->validate([
            'image_path' => 'image|mimes:jpeg,png,jpg,webp|max:20240',
            'order' => 'numeric|required|unique:banners,order',
            'status' => 'required',
        ]);


        if ($request->hasFile('image_path')) {
            $banner['image_path'] = $request->file('image_path')->store('banner', 'public');
        }

        Banner::create($banner);

        return to_route('banners.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'New Category has been added!']);
    }

    public function destroy(Banner $banner)
    {

        if ($banner->image_path) {
            // dd($banner->image_path);
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();
        return to_route('banners.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Banner has been deleted!']);
    }
}
