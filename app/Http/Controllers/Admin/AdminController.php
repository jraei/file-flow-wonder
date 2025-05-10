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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Get time period from request or default to 'week'
        $period = $request->get('period', 'week');
        $validPeriods = ['day', 'week', 'month', 'year'];

        if (!in_array($period, $validPeriods)) {
            $period = 'week';
        }

        // Cache key based on period
        $cacheKey = "admin_dashboard_{$period}";

        // Return cached data if available (5 minutes TTL)
        return Inertia::render('Admin/Dashboard', Cache::remember($cacheKey, 300, function () use ($period) {
            return [
                'metrics' => $this->getMetrics($period),
                'charts' => $this->getCharts($period),
                'tables' => $this->getTables($period),
                'period' => $period
            ];
        }));
    }

    /**
     * Get key metrics data
     */
    private function getMetrics($period)
    {
        $startDate = $this->getStartDate($period);

        // User growth metrics
        $userMetrics = $this->getUserGrowthMetrics($startDate);

        // Revenue metrics
        $revenueMetrics = $this->getRevenueMetrics($startDate);

        // Order metrics
        $orderMetrics = $this->getOrderMetrics($startDate);

        // Product metrics
        $productMetrics = $this->getProductMetrics($startDate);

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
    private function getCharts($period)
    {
        $startDate = $this->getStartDate($period);

        return [
            'revenue_trend' => $this->getRevenueTrend($startDate, $period),
            'order_stats' => $this->getOrderStats($startDate)
        ];
    }

    /**
     * Get tables data
     */
    private function getTables($period)
    {
        $startDate = $this->getStartDate($period);

        return [
            'recent_transactions' => $this->getRecentTransactions($startDate),
            'top_products' => $this->getTopProducts($startDate)
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
            default:
                return Carbon::now()->subWeek();
        }
    }

    /**
     * Get user growth metrics
     */
    private function getUserGrowthMetrics($startDate)
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
    private function getRevenueMetrics($startDate)
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
            'currency' => 'USD', // Adjust as needed
            'growthPercent' => $growthPercent,
            'isPositive' => $growthPercent >= 0
        ];
    }

    /**
     * Get order metrics
     */
    private function getOrderMetrics($startDate)
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
    private function getProductMetrics($startDate)
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
    private function getRevenueTrend($startDate, $period)
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
                ],
                [
                    'label' => 'Net Profit',
                    'data' => $revenueData->pluck('profit')->toArray(),
                    'borderColor' => '#33C3F0',
                    'backgroundColor' => 'rgba(51, 195, 240, 0.2)',
                ],
                [
                    'label' => 'Failed Transactions',
                    'data' => $revenueData->map(function ($item) use ($failedData) {
                        return $failedData->has($item->date) ? $failedData[$item->date]->count : 0;
                    })->toArray(),
                    'borderColor' => '#ea384c',
                    'backgroundColor' => 'rgba(234, 56, 76, 0.2)',
                ]
            ]
        ];
    }

    /**
     * Get order statistics
     */
    private function getOrderStats($startDate)
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
    private function getRecentTransactions($startDate)
    {
        return Pembelian::with(['user', 'layanan'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->order_id,
                    'user' => $transaction->user ? $transaction->user->username : 'Unknown',
                    'amount' => $transaction->total_price,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'game' => $transaction->layanan ? $transaction->layanan->nama_layanan : 'N/A',
                ];
            });
    }

    /**
     * Get top products
     */
    private function getTopProducts($startDate)
    {
        $topProductIds = Pembelian::select('layanan_id', DB::raw('count(*) as sales_count'))
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('layanan_id')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('sales_count', 'layanan_id')
            ->toArray();

        // Get detailed product information
        return Layanan::whereIn('id', array_keys($topProductIds))
            ->get()
            ->map(function ($product) use ($topProductIds) {
                $sales = $topProductIds[$product->id];
                $revenue = $sales * $product->price;

                // Calculate growth (placeholder - would need historical data)
                $growth = rand(5, 15) * (rand(0, 1) ? 1 : -1);

                return [
                    'id' => $product->id,
                    'name' => $product->nama_layanan,
                    'sales' => $sales,
                    'revenue' => $revenue,
                    'growth' => $growth,
                ];
            });
    }

    /**
     * Export dashboard data to Excel
     */
    public function exportDashboard(Request $request)
    {
        // Export functionality would be implemented here
        // It would generate an Excel file from the dashboard data
        // and return a download response

        // For now, just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Export functionality will be implemented here'
        ]);
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
