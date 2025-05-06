<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $casts = [
        'instruksi' => 'array',
        'expired_time' => 'datetime',
    ];

    protected $guarded = [
        'id'
    ];

    /**
     * Get the pembelian that owns the payment.
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'order_id', 'order_id');
    }

    /**
     * Check if payment has expired
     */
    public function isExpired(): bool
    {
        if (!$this->expired_time) {
            return false;
        }

        return now() > $this->expired_time;
    }

    /**
     * Check if payment is due soon (less than 15 minutes)
     */
    public function isDueSoon(): bool
    {
        if (!$this->expired_time) {
            return false;
        }

        $fifteenMinutesFromNow = now()->addMinutes(15);
        return now() < $this->expired_time && $this->expired_time < $fifteenMinutesFromNow;
    }

    /**
     * Get formatted expiry time
     */
    public function getFormattedExpiryTime(): string
    {
        if (!$this->expired_time) {
            return 'No expiry time set';
        }

        return $this->expired_time->format('Y-m-d H:i:s');
    }

    /**
     * Get time remaining until expiry in seconds
     */
    public function getTimeRemainingInSeconds(): int
    {
        if (!$this->expired_time || $this->isExpired()) {
            return 0;
        }

        return now()->diffInSeconds($this->expired_time);
    }
}
