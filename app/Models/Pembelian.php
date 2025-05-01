<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
