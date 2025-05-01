<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'price',
        'payment_link',
        'payment_method',
        'payment_reference',
        'status'
    ];

    /**
     * Get the pembelian that owns the payment.
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'order_id', 'order_id');
    }
}