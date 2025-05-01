<?php

namespace App\Models;

use App\Models\Kategori;
use App\Models\Provider;
use App\Models\ProfitProduk;
use App\Models\ItemThumbnail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function Kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function Provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function ProfitProduk(): HasMany
    {
        return $this->hasMany(ProfitProduk::class, 'produk_id');
    }

    public function layanan(): HasMany
    {
        return $this->hasMany(Layanan::class);
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(ItemThumbnail::class, 'produk_id');
    }

    public function inputFields(): HasMany
    {
        return $this->hasMany(ProdukInputField::class);
    }


    public function parsedItemCount($nama_layanan = null): ?int
    {
        // Ambil nama layanan dan convert ke lowercase dulu
        $name = strtolower($nama_layanan);

        // Cari angka + angka (kayak 10+1) → hitung total
        if (preg_match_all('/(\d+)(?:\s*\+\s*(\d+))?/', $name, $matches, PREG_SET_ORDER)) {
            $total = 0;

            foreach ($matches as $match) {
                $base = (int) $match[1];
                $bonus = isset($match[2]) ? (int) $match[2] : 0;
                $total += ($base + $bonus);
            }

            return $total;
        }

        // Kalo ga nemu angka, return null
        return null;
    }


    /**
     * Get the appropriate thumbnail for a given quantity.
     */
    public function getThumbnailForQuantity($quantity = null)
    {
        if ($quantity === null) {
            // Return the default thumbnail
            return $this->thumbnails()->where('default_for_produk', true)->first();
        }

        // First try to find a range-based thumbnail
        $thumbnail = $this->thumbnails()
            ->where('is_static', false)
            ->where('min_item', '<=', $quantity)
            ->where('max_item', '>=', $quantity)
            ->first();

        // If no range-based thumbnail, try static
        if (!$thumbnail) {
            $thumbnail = $this->thumbnails()
                ->where('is_static', true)
                ->first();
        }

        // If still no thumbnail, use default
        if (!$thumbnail) {
            $thumbnail = $this->thumbnails()
                ->where('default_for_produk', true)
                ->first();
        }

        return $thumbnail;
    }
}
