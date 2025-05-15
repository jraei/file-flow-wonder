<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashsaleEvent;
use App\Models\Layanan;
use App\Models\Pembayaran;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with metrics and visualizations
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'day');
        $startDate = null;
        $endDate = null;

        // Set date range based on period
        if ($period === 'custom') {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($period !== 'lifetime') {
            switch ($period) {
                case 'day':
                    $startDate = Carbon::today();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    break;
            }
            $endDate = Carbon::now();
        }

        // Collect dashboard data
        return Inertia::render('Admin/Dashboard', [
            'metrics' => $this->getMetrics($startDate, $endDate, $period),
            'charts' => $this->getCharts($startDate, $endDate, $period),
            'tables' => $this->getTables($startDate, $endDate),
            'period' => $period,
        ]);
    }

    /**
     * Get key metrics for the dashboard
     */
    private function getMetrics($startDate, $endDate, $period)
    {
        // User metrics
        $totalUsers = User::count();
        $newUsers = User::when($startDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Compare with previous period
        $previousStartDate = null;
        $previousEndDate = null;

        if ($startDate && $endDate) {
            $periodDuration = $endDate->diffInSeconds($startDate);
            $previousStartDate = (clone $startDate)->subSeconds($periodDuration);
            $previousEndDate = (clone $startDate)->subSecond();

            $previousNewUsers = User::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
            $userGrowthPercent = $previousNewUsers > 0 ? round((($newUsers - $previousNewUsers) / $previousNewUsers) * 100, 1) : 0;

            // Revenue metrics
            $currentRevenue = Pembelian::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_price');

            $previousRevenue = Pembelian::whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->where('status', 'completed')
                ->sum('total_price');

            $revenueGrowthPercent = $previousRevenue > 0 ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;

            // Order metrics
            $currentOrders = Pembelian::whereBetween('created_at', [$startDate, $endDate])->count();
            $previousOrders = Pembelian::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
            $orderGrowthPercent = $previousOrders > 0 ? round((($currentOrders - $previousOrders) / $previousOrders) * 100, 1) : 0;

            // Product metrics
            $activeProducts = Produk::where('status', 'active')->count();
            $previousActiveProducts = Produk::where('status', 'active')
                ->where('created_at', '<', $previousEndDate)
                ->count();
            $productGrowthPercent = $previousActiveProducts > 0 ? round((($activeProducts - $previousActiveProducts) / $previousActiveProducts) * 100, 1) : 0;
        } else {
            // Lifetime period - no comparison
            $userGrowthPercent = 0;
            $revenueGrowthPercent = 0;
            $orderGrowthPercent = 0;
            $productGrowthPercent = 0;

            // Get total revenue for all time
            $currentRevenue = Pembelian::where('status', 'completed')->sum('total_price');

            $currentOrders = Pembelian::count();
            $activeProducts = Produk::where('status', 'active')->count();
        }

        return [
            'users' => [
                'total' => $totalUsers,
                'newUsers' => $newUsers,
                'growthPercent' => $userGrowthPercent,
                'isPositive' => $userGrowthPercent >= 0,
            ],
            'revenue' => [
                'total' => $currentRevenue,
                'currency' => 'IDR',
                'growthPercent' => $revenueGrowthPercent,
                'isPositive' => $revenueGrowthPercent >= 0,
            ],
            'orders' => [
                'total' => $currentOrders,
                'growthPercent' => $orderGrowthPercent,
                'isPositive' => $orderGrowthPercent >= 0,
            ],
            'products' => [
                'total' => $activeProducts,
                'growthPercent' => $productGrowthPercent,
                'isPositive' => $productGrowthPercent >= 0,
            ],
        ];
    }

    /**
     * Get chart data for the dashboard
     */
    private function getCharts($startDate, $endDate, $period)
    {
        // Set up the time interval
        $interval = 'day';
        $format = 'Y-m-d';

        if ($period === 'day') {
            $interval = 'hour';
            $format = 'H:i';
        } elseif ($period === 'week') {
            $interval = 'day';
            $format = 'D';
        } elseif ($period === 'month') {
            $interval = 'day';
            $format = 'd M';
        } elseif ($period === 'year') {
            $interval = 'month';
            $format = 'M Y';
        } elseif ($period === 'lifetime') {
            $interval = 'month';
            $format = 'M Y';
            $startDate = Carbon::parse(Pembelian::min('created_at'));
            $endDate = Carbon::now();
        }

        // Revenue trend data
        $labels = [];
        $revenueData = [];
        $profitData = [];

        // Generate time points
        $current = clone $startDate;
        while ($current <= $endDate) {
            $labels[] = $current->format($format);

            $nextPoint = (clone $current)->add(1, $interval);

            // Get revenue and profit for this time period
            $periodRevenue = Pembelian::whereBetween('created_at', [$current, $nextPoint])
                ->where('status', 'completed')
                ->sum('total_price');

            $periodProfit = Pembelian::whereBetween('created_at', [$current, $nextPoint])
                ->where('status', 'completed')
                ->sum('profit');


            $revenueData[] = $periodRevenue;
            $profitData[] = $periodProfit;

            $current = $nextPoint;
        }

        // Order status distribution
        $statuses = ['completed', 'pending', 'processing', 'failed', 'cancelled'];
        $statusCounts = [];
        $statusColors = [
            'completed' => 'rgba(72, 187, 120, 0.7)', // green
            'pending' => 'rgba(237, 137, 54, 0.7)',   // orange
            'processing' => 'rgba(66, 153, 225, 0.7)', // blue
            'failed' => 'rgba(229, 62, 62, 0.7)',     // red
            'cancelled' => 'rgba(160, 174, 192, 0.7)', // gray
        ];

        $statusLabels = [
            'completed' => 'Completed',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        // Get counts for each status
        foreach ($statuses as $status) {
            $count = Pembelian::when($startDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })->where('status', $status)->count();

            $statusCounts[$status] = $count;
        }

        // Format for Chart.js
        $statusLabelsFormatted = [];
        $statusValues = [];
        $statusColorValues = [];

        foreach ($statusCounts as $status => $count) {
            if ($count > 0) {
                $statusLabelsFormatted[] = $statusLabels[$status];
                $statusValues[] = $count;
                $statusColorValues[] = $statusColors[$status];
            }
        }

        // Prepare chart data
        return [
            'revenue_trend' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => $revenueData,
                        'borderColor' => 'rgba(155, 135, 245, 1)',
                        'backgroundColor' => 'rgba(155, 135, 245, 0.05)',
                        'fill' => true,
                    ],
                    [
                        'label' => 'Profit',
                        'data' => $profitData,
                        'borderColor' => 'rgba(51, 195, 240, 1)',
                        'backgroundColor' => 'rgba(51, 195, 240, 0.05)',
                        'fill' => true,
                    ]
                ]
            ],
            'order_stats' => [
                'statusDistribution' => [
                    'labels' => $statusLabelsFormatted,
                    'datasets' => [
                        [
                            'data' => $statusValues,
                            'backgroundColor' => $statusColorValues,
                            'borderWidth' => 1,
                            'borderColor' => 'rgba(0, 0, 0, 0.05)',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get table data for the dashboard
     */
    private function getTables($startDate, $endDate)
    {
        // Recent transactions
        $recentTransactions = Pembelian::with(['user', 'layanan.produk'])
            ->when($startDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->latest()
            ->where('status', 'completed')
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->reference_id ?? $transaction->id,
                    'user' => $transaction->user->username ?? 'Unknown',
                    'game' => $transaction->layanan->produk->nama ?? 'Unknown',
                    'amount' => $transaction->total_price,
                    'profit' => $transaction->profit,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Top products
        $topProducts = Produk::withCount(['layanan as sales' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('pembelian', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->when($startDate, function ($q) use ($startDate, $endDate) {
                        return $q->whereBetween('created_at', [$startDate, $endDate]);
                    });
            });
        }])
            ->withSum(['layanan as revenue' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('pembelian', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                        ->when($startDate, function ($q) use ($startDate, $endDate) {
                            return $q->whereBetween('created_at', [$startDate, $endDate]);
                        });
                });
            }], 'pembelian.total_price')
            ->withSum(['layanan as profit' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('pembelian', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                        ->when($startDate, function ($q) use ($startDate, $endDate) {
                            return $q->whereBetween('created_at', [$startDate, $endDate]);
                        });
                });
            }], 'pembelian.profit')
            ->where('status', 'active')
            ->orderByDesc('sales')
            ->take(10)
            ->get()
            ->map(function ($product) {
                // Calculate growth (mock data for now)
                $growth = rand(-10, 30);

                return [
                    'id' => $product->id,
                    'name' => $product->nama,
                    'sales' => $product->sales ?? 0,
                    'revenue' => $product->revenue ?? 0,
                    'profit' => $product->profit ?? 0,
                    'growth' => $growth,
                ];
            });

        return [
            'recent_transactions' => $recentTransactions,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Get product services for the product-specific analytics
     */
    public function productServices(Request $request, $productId = null)
    {
        $period = $request->input('period', 'day');
        [$startDate, $endDate] = $this->getDateRangeFromPeriod($period, $request);

        $query = Layanan::query();

        if ($productId) {
            $query->where('produk_id', $productId);
        }

        $services = $query->withCount(['pembelian as sales' => function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed')
                ->when($startDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                });
        }])
            ->withSum(['pembelian as revenue' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->when($startDate, function ($q) use ($startDate, $endDate) {
                        return $q->whereBetween('created_at', [$startDate, $endDate]);
                    });
            }], 'total_price')
            ->withSum(['pembelian as profit' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->when($startDate, function ($q) use ($startDate, $endDate) {
                        return $q->whereBetween('created_at', [$startDate, $endDate]);
                    });
            }], 'profit')
            ->orderByDesc('sales')
            ->take(10)
            ->get()
            ->map(function ($service) {
                // Calculate growth (mock data for now)
                $growth = rand(-15, 25);

                return [
                    'id' => $service->id,
                    'name' => $service->nama_layanan,
                    'sales' => $service->sales ?? 0,
                    'revenue' => $service->revenue ?? 0,
                    'profit' => $service->profit ?? 0,
                    'growth' => $growth,
                ];
            });

        return response()->json([
            'services' => $services
        ]);
    }

    /**
     * Get products list for the dropdown
     */
    public function products()
    {
        $products = Produk::where('status', 'active')
            ->select('id', 'nama as name')
            ->orderBy('nama')
            ->get();

        return response()->json($products);
    }

    /**
     * Get flashsale events
     */
    public function flashsales(Request $request)
    {
        $period = $request->input('period', 'day');
        [$startDate, $endDate] = $this->getDateRangeFromPeriod($period, $request);

        $flashsales = FlashsaleEvent::with(['item'])
            ->when($startDate, function ($query) use ($startDate, $endDate) {
                return $query->where(function ($q) use ($startDate, $endDate) {
                    // Event is active within the time period
                    $q->whereBetween('event_start_date', [$startDate, $endDate])
                        ->orWhereBetween('event_end_date', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('event_start_date', '<=', $startDate)
                                ->where('event_end_date', '>=', $endDate);
                        });
                });
            })
            ->where('status', 'active')
            ->get()
            ->map(function ($event) {
                // Calculate mock revenue for demonstration
                $totalRevenue = $event->item->sum('discount_price') * rand(5, 50);

                // Mock top items
                $topItems = [];
                foreach ($event->item as $index => $item) {
                    if ($index < 5) {
                        $topItems[] = [
                            'id' => $item->id,
                            'service_name' => $item->layanan->nama_layanan ?? 'Unknown Service',
                            'sold' => rand(10, 100),
                        ];
                    }
                }

                return [
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_start_date' => $event->event_start_date,
                    'event_end_date' => $event->event_end_date,
                    'item' => $event->item,
                    'total_revenue' => $totalRevenue,
                    'top_items' => $topItems,
                ];
            });

        return response()->json($flashsales);
    }

    /**
     * Get voucher utilization data
     */
    public function vouchers(Request $request)
    {
        $period = $request->input('period', 'day');
        [$startDate, $endDate] = $this->getDateRangeFromPeriod($period, $request);

        $vouchers = Voucher::when($startDate, function ($query) use ($startDate, $endDate) {
            // Show vouchers valid within the time period
            return $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('valid_from', '<=', $endDate)
                    ->where(function ($q2) use ($startDate) {
                        $q2->where('expired_at', '>=', $startDate)
                            ->orWhereNull('expired_at');
                    });
            });
        })
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
     * Export dashboard data
     */
    public function export(Request $request)
    {
        $type = $request->input('type');
        $period = $request->input('period', 'day');

        // This would be implemented with a proper CSV/Excel export
        // For now just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => "Exporting {$type} data for period: {$period}",
        ]);
    }

    /**
     * Helper to get date range from period parameter
     */
    private function getDateRangeFromPeriod($period, Request $request)
    {
        $startDate = null;
        $endDate = null;

        if ($period === 'custom') {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($period !== 'lifetime') {
            switch ($period) {
                case 'day':
                    $startDate = Carbon::today();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    break;
            }
            $endDate = Carbon::now();
        }

        return [$startDate, $endDate];
    }
}
