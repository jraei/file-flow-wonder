<?php

namespace App\Models;

use App\Models\Deposit;
use App\Models\Pembelian;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class);
    }

    public function deposit(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function isAdmin(): bool
    {
        return $this->level === 'admin';
    }

    public function getActivityMetrics($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        // Get total spent and transaction count
        $metrics = $this->pembelian()
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['completed', 'processing'])
            ->selectRaw('COUNT(*) as transactions_count, SUM(total_price) as total_spent')
            ->first();

        // Get active days count (days with at least one purchase)
        $activeDaysCount = $this->pembelian()
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['completed', 'processing'])
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as active_days')
            ->first()
            ->active_days;

        return [
            'total_spent' => $metrics->total_spent ?? 0,
            'transactions_count' => $metrics->transactions_count ?? 0,
            'active_days' => $activeDaysCount ?? 0,
            'period_days' => $days,
        ];
    }

    /**
     * Get most purchased products by user
     */
    public function getFavoriteProducts($limit = 5)
    {
        return DB::table('pembelians')
            ->select('produks.nama as product_name', 'produks.id as product_id', DB::raw('COUNT(*) as purchase_count'))
            ->join('layanans', 'pembelians.layanan_id', '=', 'layanans.id')
            ->join('produks', 'layanans.produk_id', '=', 'produks.id')
            ->where('pembelians.user_id', $this->id)
            ->whereIn('pembelians.status', ['completed'])
            ->groupBy('produks.id', 'produks.nama')
            ->orderByDesc('purchase_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent user activity - includes both purchases and deposits
     */
    public function getRecentActivity($limit = 10)
    {
        // Get recent purchases
        $purchases = $this->pembelian()
            ->with(['layanan', 'layanan.produk'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'purchase',
                    'reference' => $item->reference_id ?? $item->order_id,
                    'amount' => $item->total_price,
                    'description' => $item->layanan->nama_layanan ?? 'Unknown Service',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ];
            });

        // Get recent deposits
        $deposits = $this->deposit()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'deposit',
                    'reference' => $item->deposit_id,
                    'amount' => $item->amount,
                    'description' => 'Balance top-up',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ];
            });

        // Merge and sort by date
        return $purchases->concat($deposits)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    /**
     * Get active users for the past number of days
     * Returns users who have made at least one transaction
     */
    public static function getActiveUsers($days = 30, $limit = 10)
    {
        $startDate = Carbon::now()->subDays($days);

        return self::select(
            'users.*',
            DB::raw('COUNT(pembelians.id) as transactions_count'),
            DB::raw('SUM(pembelians.total_price) as total_spent')
        )
            ->join('pembelians', 'users.id', '=', 'pembelians.user_id')
            ->where('pembelians.created_at', '>=', $startDate)
            ->whereIn('pembelians.status', ['completed', 'processing'])
            ->groupBy('users.id')
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->get();
    }
}
