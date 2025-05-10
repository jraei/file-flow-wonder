<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Pembelian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'callback_data' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'order_id', 'order_id');
    }

    public static function generateReferenceId()
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $uniqueId = $prefix . $date . $random;

        // Check if ID already exists
        while (self::where('reference_id', $uniqueId)->exists()) {
            $random = rand(1000, 9999);
            $uniqueId = $prefix . $date . $random;
        }

        return $uniqueId;
    }

    /**
     * Get top 10 users with highest total purchases for today
     */
    public static function dailyTop10()
    {
        return self::select('users.username', DB::raw('SUM(pembelians.total_price) as total'))
            ->join('users', 'users.id', '=', 'pembelians.user_id')
            ->whereDate('pembelians.created_at', Carbon::today())
            ->whereIn('pembelians.status', ['completed'])
            ->groupBy('pembelians.user_id', 'users.username')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'username' => $item->username,
                    'total' => $item->total,
                    'formatted_total' => 'Rp ' . number_format($item->total, 0, ',', '.'),
                ];
            });
    }

    /**
     * Get top 10 users with highest total purchases for this week
     */
    public static function weeklyTop10()
    {
        return self::select('users.username', DB::raw('SUM(pembelians.total_price) as total'))
            ->join('users', 'users.id', '=', 'pembelians.user_id')
            ->whereBetween('pembelians.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->whereIn('pembelians.status', ['completed'])
            ->groupBy('pembelians.user_id', 'users.username')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'username' => $item->username,
                    'total' => $item->total,
                    'formatted_total' => 'Rp ' . number_format($item->total, 0, ',', '.'),
                ];
            });
    }

    /**
     * Get top 10 users with highest total purchases for this month
     */
    public static function monthlyTop10()
    {
        return self::select('users.username', DB::raw('SUM(pembelians.total_price) as total'))
            ->join('users', 'users.id', '=', 'pembelians.user_id')
            ->whereYear('pembelians.created_at', Carbon::now()->year)
            ->whereMonth('pembelians.created_at', Carbon::now()->month)
            ->whereIn('pembelians.status', ['completed'])
            ->groupBy('pembelians.user_id', 'users.username')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'username' => $item->username,
                    'total' => $item->total,
                    'formatted_total' => 'Rp ' . number_format($item->total, 0, ',', '.'),
                ];
            });
    }

    /**
     * Scope for transactions belonging to a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Add transaction filters for data table
     * Accepts:
     * - status (pending/processing/completed/failed/cancelled)
     * - search (free text: searches order_id or layanan.nama)
     * - date_range: [start, end] (YYYY-MM-DD)
     * - sort_by: any valid column (default: created_at)
     * - sort_order: asc/desc (default: desc)
     */
    public function scopeWithFilters($query, $request)
    {
        // Filter by status
        $status = $request->input('status');
        if ($status && strtolower($status) !== 'all') {
            $query->where('status', $status);
        }

        // Filter by date range
        $start = $request->input('date_start');
        $end = $request->input('date_end');
        if ($start && $end) {
            // Coerce valid format
            $dateStart = date('Y-m-d 00:00:00', strtotime($start));
            $dateEnd = date('Y-m-d 23:59:59', strtotime($end));
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        // Search filter
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search by order_id or layanan name
                $q->where('order_id', 'like', "%$search%")
                    ->orWhereHas('layanan', function ($q) use ($search) {
                        $q->where('nama_layanan', 'like', "%$search%");
                    });
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSorts = ['order_id', 'created_at', 'price', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        return $query;
    }
}
