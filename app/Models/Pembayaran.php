
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
    ];

    protected $appends = [
        'qris_url',
    ];

    /**
     * Get the pembelian that owns the payment.
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'order_id', 'order_id');
    }

    /**
     * Get the QRIS URL.
     */
    public function getQrisUrlAttribute()
    {
        // If we have a payment_method containing QRIS and a payment_reference
        if ($this->payment_method && 
            (stripos($this->payment_method, 'qris') !== false) && 
            $this->payment_reference) {
            return url("/api/payments/{$this->payment_reference}/qr-code");
        }

        return null;
    }

    /**
     * Get status information for timeline.
     */
    public function getStatusInfo()
    {
        return [
            'status' => $this->status,
            'is_paid' => $this->status === 'paid',
            'is_pending' => $this->status === 'pending',
            'is_failed' => in_array($this->status, ['failed', 'cancelled']),
            'payment_method' => $this->payment_method,
            'has_qr' => !!$this->qris_url,
        ];
    }
}
