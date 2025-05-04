
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'expired_time' => 'datetime',
        'instruksi' => 'array',
    ];

    /**
     * Get the pembelian that owns the payment.
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'order_id', 'order_id');
    }
    
    /**
     * Get formatted payment status
     */
    public function getFormattedStatusAttribute()
    {
        $statusMap = [
            'paid' => 'Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
        ];
        
        return $statusMap[$this->status] ?? 'Unknown';
    }
    
    /**
     * Check if payment has expired
     */
    public function getHasExpiredAttribute()
    {
        if (!$this->expired_time) {
            return false;
        }
        
        return now()->greaterThan($this->expired_time);
    }
}
