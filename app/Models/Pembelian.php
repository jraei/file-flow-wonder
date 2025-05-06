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
}
