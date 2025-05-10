<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\WebConfig;
use App\Models\Provider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Layanan;
use App\Models\FlashsaleEvent;
use App\Models\FlashsaleItem;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Get time period from request or default to 'week'
        $period = $request->get('period', 'week');
        $validPeriods = ['day', 'week', 'month', 'year', 'lifetime', 'custom'];

        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Handle custom date range
        $startDate = null;
        $endDate = null;

        if ($period === 'custom') {
            $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subWeek();
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }

        // Cache key based on period and date range
        $cacheKey = "admin_dashboard_{$period}";
        if ($period === 'custom') {
            $cacheKey .= "_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        }

        // Return cached data if available (5 minutes TTL)
        return Inertia::render('Admin/Dashboard', Cache::remember($cacheKey, 300, function () use ($period, $startDate, $endDate) {
            return [
                'metrics' => $this->getMetrics($startDate, $endDate),
                'charts' => $this->getCharts($startDate, $endDate, $period),
                'tables' => $this->getTables($startDate, $endDate),
                'period' => $period
            ];
        }));
    }

    /**
     * Get products for the dashboard product selector
     */
    public function getProducts()
    {
        $products = Produk::where('status', 'active')
            ->select('id', 'nama as name')
            ->orderBy('nama')
            ->get();

        return response()->json($products);
    }

    /**
     * Get services for a specific product with analytics
     */
    public function getProductServices(Request $request, $productId = null)
    {
        $startDate = $this->getStartDate($request->get('period', 'week'));

        $query = Layanan::select(
            'layanans.id',
            'layanans.nama_layanan as name',
            DB::raw('COUNT(pembelians.id) as sales'),
            DB::raw('SUM(pembelians.total_price) as revenue'),
            DB::raw('SUM(pembelians.profit) as profit')
        )
            ->leftJoin('pembelians', 'pembelians.layanan_id', '=', 'layanans.id')
            ->where(function ($query) {
                $query->whereNull('pembelians.id')
                    ->orWhere('pembelians.status', 'completed');
            })
            ->where('pembelians.created_at', '>=', $startDate);

        if ($productId) {
            $query->where('layanans.produk_id', $productId);
        }

        $services = $query->groupBy('layanans.id', 'layanans.nama_layanan')
            ->orderByDesc(DB::raw('COUNT(pembelians.id)'))
            ->limit(10)
            ->get()
            ->map(function ($service) {
                // Calculate a random growth percentage for demonstration
                // In a real app, you'd compare to previous period
                $growth = rand(-20, 30);

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'sales' => $service->sales ?: 0,
                    'revenue' => $service->revenue ?: 0,
                    'profit' => $service->profit ?: 0,
                    'growth' => $growth,
                ];
            });

        return response()->json(['services' => $services]);
    }

    /**
     * Get active flashsale events with performance metrics
     */
    public function getFlashsales(Request $request)
    {
        $period = $request->get('period', 'week');
        $startDate = $this->getStartDate($period);

        $flashsaleEvents = FlashsaleEvent::where(function ($query) use ($startDate) {
            $query->where('event_end_date', '>=', $startDate)
                ->orWhere('status', 'active');
        })
            ->with(['item' => function ($query) {
                $query->with(['layanan:id,nama_layanan']);
            }])
            ->get();

        // Process each event to add performance data
        $processedEvents = $flashsaleEvents->map(function ($event) {
            // Calculate total revenue from items in this flashsale
            $totalRevenue = 0;
            $totalProfit = 0;

            // Get top performing items
            $topItems = collect($event->item)->map(function ($item) {
                // In a real app, you would fetch actual sales data
                // For demonstration, we're using random values
                $sold = rand(1, 50);
                $itemRevenue = $sold * $item->harga_flashsale;

                return [
                    'id' => $item->id,
                    'service_name' => $item->layanan->nama_layanan ?? 'Unknown Service',
                    'sold' => $sold,
                    'revenue' => $itemRevenue,
                ];
            })->sortByDesc('sold')->values();

            foreach ($topItems as $item) {
                $totalRevenue += $item['revenue'];
                // Assume profit is about 20% of revenue for demonstration
                $totalProfit += $item['revenue'] * 0.2;
            }

            $event->top_items = $topItems;
            $event->total_revenue = $totalRevenue;
            $event->total_profit = $totalProfit;

            return $event;
        });

        return response()->json($processedEvents);
    }

    /**
     * Get active vouchers with usage statistics
     */
    public function getVouchers()
    {
        $vouchers = Voucher::where('expired_at', '>', now())
            ->where('is_active', true)
            ->get()
            ->map(function ($voucher) {
                // Calculate utilization percentage
                $utilizationPct = $voucher->usage_limit > 0
                    ? ($voucher->usage_count / $voucher->usage_limit) * 100
                    : 0;

                return [
                    'id' => $voucher->id,
                    'kode_voucher' => $voucher->kode_voucher,
                    'nilai' => $voucher->nilai,
                    'usage_count' => $voucher->usage_count,
                    'usage_limit' => $voucher->usage_limit,
                    'utilization_pct' => $utilizationPct,
                    'expired_at' => $voucher->expired_at,
                ];
            });

        return response()->json($vouchers);
    }

    /**
     * Export dashboard data to Excel
     */
    public function exportDashboard(Request $request)
    {
        $type = $request->get('type', 'transactions');
        $period = $request->get('period', 'week');
        $startDate = $this->getStartDate($period);

        // Create a CSV download response
        $filename = "dashboard_{$type}_{$period}_" . date('Y-m-d') . ".csv";

        return new StreamedResponse(function () use ($type, $startDate) {
            $handle = fopen('php://output', 'w');

            if ($type === 'transactions') {
                // Export recent transactions
                fputcsv($handle, ['ID', 'User', 'Game', 'Amount', 'Profit', 'Date', 'Status']);

                Pembelian::with(['user', 'layanan'])
                    ->where('created_at', '>=', $startDate)
                    ->orderBy('created_at', 'desc')
                    ->chunk(100, function ($transactions) use ($handle) {
                        foreach ($transactions as $transaction) {
                            fputcsv($handle, [
                                $transaction->order_id,
                                $transaction->user ? $transaction->user->username : 'Unknown',
                                $transaction->layanan ? $transaction->layanan->nama_layanan : 'N/A',
                                $transaction->total_price,
                                $transaction->profit,
                                $transaction->created_at->format('Y-m-d'),
                                $transaction->status,
                            ]);
                        }
                    });
            } else if ($type === 'products') {
                // Export top products
                fputcsv($handle, ['Product', 'Sales', 'Revenue', 'Profit', 'Growth']);

                $topProductIds = Pembelian::select('layanan_id', DB::raw('count(*) as sales_count'))
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'completed')
                    ->groupBy('layanan_id')
                    ->orderBy('sales_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->pluck('sales_count', 'layanan_id')
                    ->toArray();

                // Get detailed product information
                $products = Layanan::whereIn('id', array_keys($topProductIds))->get();

                foreach ($products as $product) {
                    $sales = $topProductIds[$product->id];
                    $revenue = $sales * $product->getHargaLayanan();
                    $profit = rand($revenue * 0.1, $revenue * 0.3); // Placeholder
                    $growth = rand(-15, 25); // Placeholder

                    fputcsv($handle, [
                        $product->nama_layanan,
                        $sales,
                        $revenue,
                        $profit,
                        $growth,
                    ]);
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get key metrics data
     */
    private function getMetrics($startDate, $endDate)
    {
        // User growth metrics
        $userMetrics = $this->getUserGrowthMetrics($startDate, $endDate);

        // Revenue metrics
        $revenueMetrics = $this->getRevenueMetrics($startDate, $endDate);

        // Order metrics
        $orderMetrics = $this->getOrderMetrics($startDate, $endDate);

        // Product metrics
        $productMetrics = $this->getProductMetrics($startDate, $endDate);

        return [
            'users' => $userMetrics,
            'revenue' => $revenueMetrics,
            'orders' => $orderMetrics,
            'products' => $productMetrics
        ];
    }

    /**
     * Get charts data
     */
    private function getCharts($startDate, $endDate, $period)
    {
        return [
            'revenue_trend' => $this->getRevenueTrend($startDate, $endDate, $period),
            'order_stats' => $this->getOrderStats($startDate, $endDate)
        ];
    }

    /**
     * Get tables data
     */
    private function getTables($startDate, $endDate)
    {
        return [
            'recent_transactions' => $this->getRecentTransactions($startDate, $endDate),
            'top_products' => $this->getTopProducts($startDate, $endDate)
        ];
    }

    /**
     * Get start date based on time period
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'day':
                return Carbon::now()->subDay();
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            case 'lifetime':
                return Carbon::createFromDate(2000, 1, 1); // Effectively no limit
            default:
                return Carbon::now()->subWeek();
        }
    }

    /**
     * Get user growth metrics
     */
    private function getUserGrowthMetrics($startDate, $endDate)
    {
        $totalUsers = User::count();
        $previousPeriodUsers = User::where('created_at', '<', $startDate)->count();
        $newUsers = $totalUsers - $previousPeriodUsers;

        $growthPercent = $previousPeriodUsers > 0
            ? round(($newUsers / $previousPeriodUsers) * 100, 2)
            : 100;

        return [
            'total' => $totalUsers,
            'growthPercent' => $growthPercent,
            'isPositive' => $growthPercent >= 0,
            'newUsers' => $newUsers
        ];
    }

    /**
     * Get revenue metrics
     */
    private function getRevenueMetrics($startDate, $endDate)
    {
        // Get total revenue from successful transactions
        $totalRevenue = Pembelian::where('status', 'completed')
            ->sum('total_price');

        // Get revenue from previous period
        $previousRevenue = Pembelian::where('status', 'completed')
            ->where('created_at', '<', $startDate)
            ->sum('total_price');

        // Calculate current period revenue
        $currentRevenue = $totalRevenue - $previousRevenue;

        // Calculate growth
        $growthPercent = $previousRevenue > 0
            ? round((($currentRevenue / $previousRevenue) * 100), 2)
            : 100;

        return [
            'total' => $totalRevenue,
            'currency' => 'IDR', // Adjust as needed
            'growthPercent' => $growthPercent,
            'isPositive' => $growthPercent >= 0
        ];
    }

    /**
     * Get order metrics
     */
    private function getOrderMetrics($startDate, $endDate)
    {
        // Get total orders
        $totalOrders = Pembelian::count();

        // Get orders from previous period
        $previousOrders = Pembelian::where('created_at', '<', $startDate)->count();

        // Calculate current period orders
        $currentOrders = $totalOrders - $previousOrders;

        // Calculate growth
        $growthPercent = $previousOrders > 0
            ? round((($currentOrders / $previousOrders) * 100), 2)
            : 100;

        return [
            'total' => $totalOrders,
            'growthPercent' => $growthPercent,
            'isPositive' => $growthPercent >= 0
        ];
    }

    /**
     * Get product metrics
     */
    private function getProductMetrics($startDate, $endDate)
    {
        // Get active products count
        $totalProducts = Produk::where('status', 'active')->count();

        // Get products from previous period
        $previousProducts = Produk::where('status', 'active')
            ->where('created_at', '<', $startDate)
            ->count();

        // Calculate growth
        $growthPercent = $previousProducts > 0
            ? round((($totalProducts - $previousProducts) / $previousProducts) * 100, 2)
            : 100;

        return [
            'total' => $totalProducts,
            'growthPercent' => $growthPercent,
            'isPositive' => $growthPercent >= 0
        ];
    }

    /**
     * Get revenue trend data
     */
    private function getRevenueTrend($startDate, $endDate, $period)
    {
        $format = '%Y-%m-%d';
        $groupBy = 'date';

        if ($period === 'year') {
            $format = '%Y-%m';
            $groupBy = 'month';
        } elseif ($period === 'day') {
            $format = '%Y-%m-%d %H:00:00';
            $groupBy = 'hour';
        }

        // Get revenue data
        $revenueData = Pembelian::select(
            DB::raw("DATE_FORMAT(created_at, '{$format}') as {$groupBy}"),
            DB::raw('SUM(total_price) as revenue'),
            DB::raw('SUM(profit) as profit')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy($groupBy)
            ->orderBy($groupBy, 'asc')
            ->get();

        // Get failed transactions
        $failedData = Pembelian::select(
            DB::raw("DATE_FORMAT(created_at, '{$format}') as {$groupBy}"),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', 'failed')
            ->where('created_at', '>=', $startDate)
            ->groupBy($groupBy)
            ->orderBy($groupBy, 'asc')
            ->get()
            ->keyBy($groupBy);

        return [
            'labels' => $revenueData->pluck($groupBy)->toArray(),
            'datasets' => [
                [
                    'label' => 'Gross Revenue',
                    'data' => $revenueData->pluck('revenue')->toArray(),
                    'borderColor' => '#9b87f5',
                    'backgroundColor' => 'rgba(155, 135, 245, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net Profit',
                    'data' => $revenueData->pluck('profit')->toArray(),
                    'borderColor' => '#33C3F0',
                    'backgroundColor' => 'rgba(51, 195, 240, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Failed Transactions',
                    'data' => $revenueData->map(function ($item) use ($failedData, $groupBy) {
                        return $failedData->has($item->$groupBy) ? $failedData[$item->$groupBy]->count : 0;
                    })->toArray(),
                    'borderColor' => '#ea384c',
                    'backgroundColor' => 'rgba(234, 56, 76, 0.2)',
                    'tension' => 0.4,
                ]
            ]
        ];
    }

    /**
     * Get order statistics
     */
    private function getOrderStats($startDate, $endDate)
    {
        // Get order status distribution
        $statusDistribution = Pembelian::select('status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            })
            ->toArray();

        // Define colors for statuses
        $statusColors = [
            'pending' => '#FEC6A1',    // Soft Orange
            'processing' => '#FEF7CD', // Soft Yellow
            'completed' => '#F2FCE2',  // Soft Green
            'failed' => '#ea384c',     // Red
            'cancelled' => '#8E9196'   // Neutral Gray
        ];

        return [
            'statusDistribution' => [
                'labels' => array_keys($statusDistribution),
                'datasets' => [
                    [
                        'data' => array_values($statusDistribution),
                        'backgroundColor' => array_map(function ($status) use ($statusColors) {
                            return $statusColors[$status] ?? '#8E9196';
                        }, array_keys($statusDistribution)),
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
    }

    /**
     * Get recent transactions
     */
    private function getRecentTransactions($startDate, $endDate)
    {
        return Pembelian::with(['user', 'layanan'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->order_id,
                    'user' => $transaction->user ? $transaction->user->username : 'Unknown',
                    'amount' => $transaction->total_price,
                    'profit' => $transaction->profit,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'game' => $transaction->layanan ? $transaction->layanan->nama_layanan : 'N/A',
                ];
            });
    }

    /**
     * Get top products
     */
    private function getTopProducts($startDate, $endDate)
    {
        // First, get layanan_id and count for top services
        $topLayananIds = Pembelian::select('layanan_id', DB::raw('count(*) as sales_count'))
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('layanan_id')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        // Get the related layanan with their produk
        $layananWithProduk = Layanan::with('produk')
            ->whereIn('id', $topLayananIds->pluck('layanan_id'))
            ->get();

        // Group by produk_id to aggregate at product level
        $productData = [];
        foreach ($layananWithProduk as $layanan) {
            $produkId = $layanan->produk_id;
            if (!isset($productData[$produkId])) {
                $productData[$produkId] = [
                    'id' => $produkId,
                    'name' => $layanan->produk ? $layanan->produk->nama : 'Unknown',
                    'sales' => 0,
                    'revenue' => 0,
                    'profit' => 0,
                ];
            }

            // Find the sales count for this layanan
            $salesCount = $topLayananIds->firstWhere('layanan_id', $layanan->id);

            if ($salesCount) {
                $sales = $salesCount->sales_count;
                $productData[$produkId]['sales'] += $sales;

                // Estimate revenue and profit
                $revenue = $sales * $layanan->getHargaLayanan();
                $productData[$produkId]['revenue'] += $revenue;

                // For profit, we can get the actual profit from pembelians
                // But we'll use the count-based estimate for this example
                $profit = Pembelian::where('layanan_id', $layanan->id)
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'completed')
                    ->sum('profit');

                $productData[$produkId]['profit'] += $profit;
            }
        }

        // Sort by sales count and take top 5
        usort($productData, function ($a, $b) {
            return $b['sales'] - $a['sales'];
        });

        $productData = array_slice($productData, 0, 5);

        // Add growth metrics (in real app, this would compare to previous period)
        foreach ($productData as &$product) {
            $product['growth'] = rand(-15, 25); // Random for demonstration
        }

        return $productData;
    }

    public function settings()
    {
        $generalSettings = [
            'judul_web' => WebConfig::get('judul_web'),
            'meta_title' => WebConfig::get('meta_title'),
            'meta_description' => WebConfig::get('meta_description'),
            'meta_keywords' => WebConfig::get('meta_keywords'),
            'support_instagram' => WebConfig::get('support_instagram'),
            'support_whatsapp' => WebConfig::get('support_whatsapp'),
            'support_email' => WebConfig::get('support_email'),
            'support_youtube' => WebConfig::get('support_youtube'),
            'support_facebook' => WebConfig::get('support_facebook'),
        ];

        $appearanceSettings = [
            'primary_color' => WebConfig::get('primary_color'),
            'primary_hover' => WebConfig::get('primary_hover'),
            'secondary_color' => WebConfig::get('secondary_color'),
            'secondary_hover' => WebConfig::get('secondary_hover'),
            'content_bg' => WebConfig::get('content_bg'),
            'footer_bg' => WebConfig::get('footer_bg'),
            'header_bg' => WebConfig::get('header_bg'),
            'text_primary' => WebConfig::get('text_primary'),
            'text_secondary' => WebConfig::get('text_secondary'),
            'logo_header' => WebConfig::get('logo_header'),
            'logo_footer' => WebConfig::get('logo_footer'),
            'logo_favicon' => WebConfig::get('logo_favicon'),
        ];

        // $appearanceSettings = WebConfig::getColorPaletteAttribute();

        $providers = Provider::select('id', 'provider_name', 'api_username', 'api_key', 'api_private_key', 'balance', 'status')->get();

        return Inertia::render('Admin/WebConfigs', [
            'generalSettings' => $generalSettings,
            'appearanceSettings' => $appearanceSettings,
            'providers' => $providers,
        ]);
    }

    public function updateGeneralSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_web' => 'required|min:3|string',
            'meta_title' => 'required|min:3|string|max:60',
            'meta_description' => 'required|min:3|string|max:160',
            'meta_keywords' => 'required|min:3|string',
            'support_instagram' => 'required|string|url',
            'support_whatsapp' => 'required|string|regex:/^[0-9]{10,15}$/',
            'support_email' => 'required|email',
            'support_youtube' => 'required|string|url',
            'support_facebook' => 'required|string|url',
        ], [
            'meta_title.max' => 'Meta title should be under 60 characters for SEO optimization',
            'meta_description.max' => 'Meta description should be under 160 characters for SEO optimization',
            'support_whatsapp.regex' => 'WhatsApp number should be numeric and between 10-15 digits',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Process each setting in a single transaction
            \DB::transaction(function () use ($request) {
                foreach ($request->all() as $key => $value) {
                    // Sanitize value
                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                    WebConfig::set($key, $value, "General setting: {$key}");
                }
            });

            // Clear config cache
            Cache::forget('web_config');

            return to_route('admin.settings')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'General settings updated successfully!']);
        } catch (\Exception $e) {
            return to_route('admin.settings')->with('status', ['type' => 'error', 'action' => 'Failed', 'text' => 'An error occurred while updating settings: ' . $e->getMessage()]);
        }
    }

    public function updateAppearance(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'primary_color' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'primary_hover' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'secondary_color' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'secondary_hover' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'content_bg' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'footer_bg' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'header_bg' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'text_primary' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'text_secondary' => 'required|string|regex:/^\#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
            'logo_header' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048|dimensions:max_width=300,max_height=100',
            'logo_footer' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048|dimensions:max_width=300,max_height=100',
            'logo_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024|dimensions:width=32,height=32',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            \DB::transaction(function () use ($request) {
                // Process color settings
                foreach (WebConfig::ALLOWED_COLORS as $color) {
                    if ($request->has($color)) {
                        WebConfig::set($color, $request->$color, "Color setting: {$color}", 'color');
                    }
                }

                // Process logo uploads
                $logoFields = ['logo_header', 'logo_footer', 'logo_favicon'];
                foreach ($logoFields as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('logos', $filename, 'public');
                        WebConfig::set($field, '/storage/' . $path, "Logo: {$field}", 'image');
                    }
                }
            });

            // Clear config cache
            Cache::forget('web_config');

            return to_route('admin.settings')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Appearance settings updated successfully!']);
        } catch (\Exception $e) {
            return to_route('admin.settings')->with('status', ['type' => 'error', 'action' => 'Failed', 'text' => 'An error occurred while updating appearance: ' . $e->getMessage()]);
        }
    }

    public function updateApiConnections(Request $request)
    {
        $providers = Provider::all();
        $rules = [];
        $messages = [];

        // Build dynamic validation rules for each provider
        foreach ($providers as $provider) {
            $providerId = $provider->id;
            $rules["providers.{$providerId}.api_username"] = 'nullable|string|max:255';
            $rules["providers.{$providerId}.api_key"] = 'nullable|string|max:255';
            $rules["providers.{$providerId}.api_private_key"] = 'nullable|string|max:255';
            $rules["providers.{$providerId}.status"] = 'required|in:active,inactive';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            if ($request->has('providers')) {
                foreach ($request->providers as $id => $data) {
                    $provider = Provider::find($id);
                    if ($provider) {
                        // Only update fields that are provided
                        $updateData = [];
                        if (isset($data['api_username'])) {
                            $updateData['api_username'] = $data['api_username'];
                        }
                        if (isset($data['api_key'])) {
                            $updateData['api_key'] = $data['api_key'];
                        }
                        if (isset($data['api_private_key'])) {
                            $updateData['api_private_key'] = $data['api_private_key'];
                        }
                        if (isset($data['status'])) {
                            $updateData['status'] = $data['status'];
                        }

                        $provider->update($updateData);
                    }
                }
            }

            return to_route('admin.settings')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'API connection settings updated successfully!']);
        } catch (\Exception $e) {
            return to_route('admin.settings')->with('status', ['type' => 'error', 'action' => 'Failed', 'text' => 'An error occurred while updating API settings: ' . $e->getMessage()]);
        }
    }

    public function deleteLogo(Request $request, $field)
    {
        $allowedFields = ['logo_header', 'logo_footer', 'logo_favicon'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['message' => 'Invalid field'], 400);
        }

        $config = WebConfig::where('key', $field)->first();

        if ($config && $config->value) {
            // Hapus file dari storage jika ada
            $filePath = str_replace('/storage/', '', $config->value);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Hapus dari database
            $config->delete();

            // Clear cache jika perlu
            Cache::forget('web_config');
        }

        return to_route('admin.settings')->with('status', [
            'type' => 'success',
            'action' => 'Logo Deleted',
            'text' => ucfirst(str_replace('_', ' ', $field)) . ' berhasil dihapus!'
        ]);
    }

    public function categories()
    {
        return Inertia::render('Admin/Categories');
    }

    public function banners()
    {
        return Inertia::render('Admin/Banners');
    }
}
