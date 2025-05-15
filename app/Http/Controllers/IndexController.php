<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Banner;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\ItemThumbnail;
use App\Models\FlashsaleEvent;
use Illuminate\Support\Facades\RateLimiter;

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

        // $activeEvents->item->load('layanan.produk'); // pastikan relasi sudah diload

        $activeEvents->item = $activeEvents->item->map(function ($item) {
            // Modifikasi langsung objek layanan
            $layanan = $item->layanan;

            preg_match('/(\d+)/', $layanan->nama_layanan, $matches);
            $quantity = isset($matches[1]) ? (int) $matches[1] : null;

            $thumbnail = null;
            if ($quantity !== null) {
                $thumbnail = ItemThumbnail::findThumbnailForQuantity($layanan->produk_id, $quantity);
            }

            if (!$thumbnail) {
                $thumbnail = ItemThumbnail::where('produk_id', $layanan->produk_id)
                    ->default()
                    ->first();
            }

            // Tambahkan thumbnail langsung ke layanan
            $item->layanan = array_merge($layanan->toArray(), [
                'thumbnail' => $thumbnail->image_path ?? null,
            ]);

            return $item->toArray();
        });
        // dd($activeEvents->item[0]->layanan);
        // Force seluruh objek jadi array
        $activeEvents = $activeEvents->toArray();
        // dd($activeEvents['item'][0]['layanan']);



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


        // dd($activeEvents->item[0]->layanan['thumbnail']);

        // dd($activeEvents->item[0]);
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

    public function leaderboard()
    {
        return Inertia::render('Leaderboard', [
            'daily' => Pembelian::dailyTop10(),
            'weekly' => Pembelian::weeklyTop10(),
            'monthly' => Pembelian::monthlyTop10(),
            'serverTime' => Carbon::now()->toISOString(),
        ]);
    }

    public function cekTransaksi(Request $request)
    {
        // Rate limiting for search - 5 requests per minute per IP
        if ($request->invoice) {
            $key = 'search_invoice:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 5)) {
                return Inertia::render('CekTransaksi', [
                    'transactions' => [],
                    'searchResult' => null,
                    'searchQuery' => $request->invoice,
                    'serverTime' => Carbon::now()->toISOString(),
                    'error' => 'Too many search attempts. Please try again later.',
                ]);
            }

            RateLimiter::hit($key, 60); // Limit expires after 60 seconds
        }

        // Get today's transactions with masking
        $liveTransactions = Pembelian::whereDate('created_at', Carbon::today())
            ->orderByDesc('created_at')
            ->with(['layanan' => function ($q) {
                $q->select('id', 'nama_layanan', 'produk_id')
                    ->with(['produk' => function ($q) {
                        $q->select('id', 'nama', 'thumbnail');
                    }]);
            }])
            ->paginate(10);

        // Apply data masking for security
        $liveTransactions->getCollection()->transform(function ($transaction) {
            // Mask order_id (invoice)
            $transaction->masked_order_id = substr($transaction->order_id, 0, 2) . '...' . substr($transaction->order_id, -3);

            // Mask phone if exists
            if ($transaction->phone) {
                $phone = $transaction->phone;
                if (strlen($phone) > 6) {
                    $transaction->masked_phone = substr($phone, 0, 3) . str_repeat('x', strlen($phone) - 6) . substr($phone, -3);
                } else {
                    $transaction->masked_phone = substr($phone, 0, 2) . str_repeat('x', max(strlen($phone) - 2, 0));
                }
            } else {
                $transaction->masked_phone = '-';
            }

            // Format and mask price
            $priceStr = number_format($transaction->total_price, 0, ',', '.');
            if (strlen($priceStr) > 4) {
                $transaction->masked_price = 'Rp ' . substr($priceStr, 0, strlen($priceStr) - 3) . 'xxx';
            } else {
                $transaction->masked_price = 'Rp ' . $priceStr;
            }

            return $transaction;
        });

        // Search for specific invoice if provided
        $searchResult = null;
        if ($request->invoice) {
            $searchResult = Pembelian::where('order_id', $request->invoice)
                ->orWhere('reference_id', $request->invoice)
                ->with([
                    'layanan' => function ($q) {
                        $q->select('id', 'nama_layanan', 'produk_id')
                            ->with(['produk' => function ($q) {
                                $q->select('id', 'nama', 'thumbnail');
                            }]);
                    },
                    'pembayaran'
                ])
                ->first();
        }

        return Inertia::render('CekTransaksi', [
            'transactions' => $liveTransactions,
            'searchResult' => $searchResult,
            'searchQuery' => $request->invoice,
            'serverTime' => Carbon::now()->toISOString(),
            'error' => null,
        ]);
    }
}
