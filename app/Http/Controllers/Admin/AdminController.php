<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\WebConfig;
use App\Models\Provider;
use App\Models\User;
use App\Models\Pembelian; // For orders
use App\Models\Produk; // For products
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with dynamic data
     */
    public function index(Request $request)
    {
        // Get time period (default to 'weekly')
        $period = $request->input('period', 'weekly');
        $validPeriods = ['daily', 'weekly', 'monthly', 'yearly'];
        
        if (!in_array($period, $validPeriods)) {
            $period = 'weekly';
        }
        
        // Cache key includes period to cache different time ranges
        $cacheKey = "admin_dashboard_data_{$period}";
        $cacheTTL = 15; // 15 minutes
        
        // Get data from cache or generate if not available
        $dashboardData = Cache::remember($cacheKey, $cacheTTL * 60, function () use ($period) {
            return [
                'metrics' => $this->getMetrics($period),
                'charts' => $this->getChartData($period),
                'tables' => $this->getTableData($period),
            ];
        });
        
        return Inertia::render('Admin/Dashboard', [
            'dashboardData' => $dashboardData,
            'activePeriod' => $period,
        ]);
    }

    /**
     * Get key metrics based on period
     */
    private function getMetrics($period)
    {
        $dateRange = $this->getDateRange($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Calculate previous period for comparisons
        $previousStartDate = (clone $startDate)->sub($this->getPeriodDuration($period));
        $previousEndDate = (clone $endDate)->sub($this->getPeriodDuration($period));
        
        // Current period data
        $currentUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousUsers = User::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        
        $currentOrders = Pembelian::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousOrders = Pembelian::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        
        $currentRevenue = Pembelian::whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $previousRevenue = Pembelian::whereBetween('created_at', [$previousStartDate, $previousEndDate])->sum('total');
        
        $currentProducts = Produk::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousProducts = Produk::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        
        // Calculate growth percentages
        $userGrowth = $this->calculateGrowth($currentUsers, $previousUsers);
        $orderGrowth = $this->calculateGrowth($currentOrders, $previousOrders);
        $revenueGrowth = $this->calculateGrowth($currentRevenue, $previousRevenue);
        $productGrowth = $this->calculateGrowth($currentProducts, $previousProducts);
        
        return [
            'users' => [
                'total' => User::count(),
                'new' => $currentUsers,
                'growthPercent' => $userGrowth,
                'isPositive' => $userGrowth >= 0,
            ],
            'revenue' => [
                'total' => Pembelian::sum('total'),
                'period' => $currentRevenue,
                'currency' => 'USD',
                'growthPercent' => $revenueGrowth,
                'isPositive' => $revenueGrowth >= 0,
            ],
            'orders' => [
                'total' => Pembelian::count(),
                'new' => $currentOrders,
                'growthPercent' => $orderGrowth,
                'isPositive' => $orderGrowth >= 0,
            ],
            'products' => [
                'total' => Produk::where('status', 'active')->count(),
                'new' => $currentProducts,
                'growthPercent' => $productGrowth,
                'isPositive' => $productGrowth >= 0,
            ],
        ];
    }
    
    /**
     * Get chart data based on period
     */
    private function getChartData($period)
    {
        $dateRange = $this->getDateRange($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Get daily revenue data for the last 30 days
        $revenueTrend = $this->getDailyRevenueTrend($startDate, $endDate);
        
        // Get order distribution by product category
        $orderDistribution = $this->getOrderDistribution();
        
        return [
            'revenue_trend' => $revenueTrend,
            'order_distribution' => $orderDistribution,
        ];
    }
    
    /**
     * Get tabular data based on period
     */
    private function getTableData($period)
    {
        $dateRange = $this->getDateRange($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Get recent transactions
        $recentTransactions = Pembelian::with(['user', 'produk'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user' => $transaction->user ? $transaction->user->name : 'Unknown',
                    'amount' => $transaction->total,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'game' => $transaction->produk ? $transaction->produk->nama : 'Unknown',
                ];
            });
        
        // Get top selling products
        $topProducts = Produk::withCount(['pembelian' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->withSum(['pembelian' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }], 'total')
        ->orderBy('pembelian_count', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($product) {
            // Calculate growth compared to previous period
            // This is simplified - in production you'd compare with previous period
            $growth = rand(-5, 15); // Placeholder for growth calculation
            
            return [
                'id' => $product->id,
                'name' => $product->nama,
                'sales' => $product->pembelian_count ?? 0,
                'revenue' => $product->pembelian_sum_total ?? 0,
                'growth' => $growth,
            ];
        });
        
        return [
            'recent_transactions' => $recentTransactions,
            'top_products' => $topProducts,
        ];
    }
    
    /**
     * Helper to get date range based on period
     */
    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'daily':
                return [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            case 'weekly':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now(),
                ];
            case 'monthly':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now(),
                ];
            case 'yearly':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now(),
                ];
            default:
                return [
                    'start' => Carbon::now()->subDays(7),
                    'end' => Carbon::now(),
                ];
        }
    }
    
    /**
     * Helper to get period duration for comparison calculations
     */
    private function getPeriodDuration($period)
    {
        switch ($period) {
            case 'daily':
                return new \DateInterval('P1D');
            case 'weekly':
                return new \DateInterval('P7D');
            case 'monthly':
                return new \DateInterval('P1M');
            case 'yearly':
                return new \DateInterval('P1Y');
            default:
                return new \DateInterval('P7D');
        }
    }
    
    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    /**
     * Get daily revenue trend
     */
    private function getDailyRevenueTrend($startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate) + 1;
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $days
        );
        
        $revenueTrend = [];
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dayStart = Carbon::parse($dateString)->startOfDay();
            $dayEnd = Carbon::parse($dateString)->endOfDay();
            
            $dailyRevenue = Pembelian::whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total');
            
            $revenueTrend[] = [
                'date' => $dateString,
                'revenue' => $dailyRevenue,
            ];
        }
        
        return $revenueTrend;
    }
    
    /**
     * Get order distribution by product category
     */
    private function getOrderDistribution()
    {
        $categories = \DB::table('kategoris')
            ->join('produks', 'kategoris.id', '=', 'produks.kategori_id')
            ->join('pembelians', 'produks.id', '=', 'pembelians.produk_id')
            ->select('kategoris.nama as name', \DB::raw('COUNT(pembelians.id) as count'))
            ->groupBy('kategoris.nama')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
            
        return $categories;
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
