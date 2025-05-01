<?php

namespace App\Models;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemThumbnail extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the product that owns the thumbnail.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Scope a query to only include default thumbnails.
     */
    public function scopeDefault($query)
    {
        return $query->where('default_for_produk', true);
    }

    /**
     * Scope a query to only include static thumbnails.
     */
    public function scopeStatic($query)
    {
        return $query->where('is_static', true);
    }

    /**
     * Scope a query to only include range-based thumbnails.
     */
    public function scopeRangeBased($query)
    {
        return $query->where('is_static', false);
    }

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Handle relative paths, storage paths, etc.
        return asset('storage/' . $this->image_path);
    }

    /**
     * Find the appropriate thumbnail for a product and quantity.
     */
    public static function findThumbnailForQuantity($produkId, $quantity)
    {
        // First try to find a range-based thumbnail
        $thumbnail = self::where('produk_id', $produkId)
            ->rangeBased()
            ->where('min_item', '<=', $quantity)
            ->where('max_item', '>=', $quantity)
            ->first();

        // If no range-based thumbnail, try static
        if (!$thumbnail) {
            $thumbnail = self::where('produk_id', $produkId)
                ->static()
                ->first();
        }

        // If still no thumbnail, use default
        if (!$thumbnail) {
            $thumbnail = self::where('produk_id', $produkId)
                ->default()
                ->first();
        }

        return $thumbnail;
    }
}
