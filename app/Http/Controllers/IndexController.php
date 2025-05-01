<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\FlashsaleEvent;
use App\Models\Produk;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status', 'active')
            ->orderBy('order')
            ->get(['id', 'image_path']);

        // Get user role ID for price calculations
        $userRoleId = auth()->check() ? auth()->user()->user_role_id : null;

        // Fetch active flash sale events with optimized query
        $activeEvents = FlashsaleEvent::where('status', 'active')
            ->where('event_start_date', '<=', now())
            ->where('event_end_date', '>=', now())
            ->with(['item' => function ($query) use ($userRoleId) {
                $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->where('stok_tersedia', '>', 0)
                            ->orWhereNull('stok_tersedia');
                    })
                    ->with(['layanan' => function ($q) use ($userRoleId) {
                        $q->where('status', 'active');
                        $q->with(['produk', 'provider']);
                    }]);
            }])
            ->first();

        // Fetch popular products
        $popularProducts = Produk::whereHas('kategori', function ($query) {
            $query->where('kategori_name', 'Populer Sekarang');
        })
            ->where('status', 'active')
            ->with(['kategori'])
            ->limit(12)
            ->get();

        // Fetch all categories except "Populer Sekarang" with product counts
        $categories = Kategori::where('status', 'active')
            ->whereHas('produk', function ($query) {
                $query->where('status', 'active'); // Optional: kalo mau hanya layanan aktif
            })
            ->where('kategori_name', '!=', 'Populer Sekarang')
            ->withCount(['produk' => function ($query) {
                $query->where('status', 'active');
            }])
            ->having('produk_count', '>', 0)
            ->get();

        // Fetch all products for catalog
        $catalogProducts = Produk::where('status', 'active')
            ->whereHas('layanan', function ($query) {
                $query->where('status', 'active'); // Optional: kalo mau hanya layanan aktif
            })
            ->with(['kategori'])
            ->get(['id', 'nama', 'slug', 'developer', 'thumbnail', 'kategori_id']);

        return Inertia::render('Index', [
            'banners' => $banners,
            'flashsaleEvent' => $activeEvents,
            'serverTime' => Carbon::now()->toISOString(),
            'userRoleId' => $userRoleId,
            'popularProducts' => $popularProducts,
            'categories' => $categories,
            'catalogProducts' => $catalogProducts,
        ]);
    }
}