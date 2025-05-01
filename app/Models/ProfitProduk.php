<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitProduk extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function user_role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_roles_id');
    }

    /**
     * Calculate the final price based on profit type and value
     *
     * @param float $basePrice The base price to apply profit rules to
     * @return float|string The calculated price or error message
     */
    public function calculatePrice($basePrice)
    {
        if (!is_numeric($basePrice)) {
            return "Error: Invalid base price";
        }
        
        $basePrice = (float) $basePrice;
        
        if ($this->type === 'percent') {
            // Add percentage to base price
            return $basePrice + ($basePrice * $this->value / 100);
        } elseif ($this->type === 'multiplier') {
            // Multiply base price by value
            return $basePrice * $this->value;
        }
        
        // Default fallback if type is not recognized
        return $basePrice;
    }
    
    /**
     * Calculate profit amount from base price
     *
     * @param float $basePrice The base price
     * @return float The profit amount
     */
    public function calculateProfitAmount($basePrice)
    {
        $finalPrice = $this->calculatePrice($basePrice);
        return $finalPrice - $basePrice;
    }
    
    /**
     * Calculate profit percentage from base price
     *
     * @param float $basePrice The base price
     * @return float The profit percentage
     */
    public function calculateProfitPercentage($basePrice)
    {
        if ($basePrice <= 0) {
            return 0;
        }
        
        $profitAmount = $this->calculateProfitAmount($basePrice);
        return ($profitAmount / $basePrice) * 100;
    }
}