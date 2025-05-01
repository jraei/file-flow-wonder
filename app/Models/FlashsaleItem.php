<?php

namespace App\Models;

use App\Models\Layanan;
use App\Models\FlashsaleEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlashsaleItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'harga_flashsale' => 'integer',
        'stok_tersedia' => 'integer',
        'stok_terjual' => 'integer',
        'batas_user' => 'integer',
    ];

    public function flashsaleEvent()
    {
        return $this->belongsTo(FlashsaleEvent::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    // Check if stock is available
    public function isStockAvailable()
    {
        return $this->stok_tersedia > 0 || $this->stok_tersedia === null;
    }

    // Check if the item is active
    public function isActive()
    {
        return $this->status === 'active' && $this->flashsaleEvent->isActive();
    }

    // Calculate stock percentage remaining
    public function getStockPercentage()
    {
        // If no stock tracking, return 100%
        if ($this->stok_tersedia === null) {
            return 100;
        }

        $total = $this->stok_tersedia + ($this->stok_terjual ?? 0);

        // Prevent division by zero
        if ($total <= 0) {
            return 0;
        }

        $percentage = ($this->stok_tersedia / $total) * 100;
        return max(0, min(100, $percentage)); // Clamp between 0-100
    }
}