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

    /**
     * List of available validation games
     */
    public static function getValidationGamesList()
    {
        return [
            '8 Ball Pool',
            'AOV',
            'Apex Legends',
            'Call Of Duty',
            'Dragon City',
            'Dragon Raja',
            'Free Fire',
            'Genshin Impact',
            'Higgs Domino',
            'Honkai Impact',
            'Lords Mobile',
            'Marvel Super War',
            'Mobile Legends',
            'Mobile Legends Adventure',
            'Point Blank',
            'Ragnarok M',
            'Tom Jerry Chase',
            'Top Eleven',
            'Valorant',
        ];
    }

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
        $name = strtolower($nama_layanan);

        if (preg_match_all('/(\d+)(?:\s*\+\s*(\d+))?/', $name, $matches, PREG_SET_ORDER)) {
            $total = 0;

            foreach ($matches as $match) {
                $base = (int) $match[1];
                $bonus = isset($match[2]) ? (int) $match[2] : 0;
                $total += ($base + $bonus);
            }

            return $total;
        }

        return null;
    }


    /**
     * Get the appropriate thumbnail for a given quantity.
     */
    public function getThumbnailForQuantity($quantity = null)
    {
        if ($quantity === null) {
            return $this->thumbnails()->where('default_for_produk', true)->first();
        }

        $thumbnail = $this->thumbnails()
            ->where('is_static', false)
            ->where('min_item', '<=', $quantity)
            ->where('max_item', '>=', $quantity)
            ->first();

        if (!$thumbnail) {
            $thumbnail = $this->thumbnails()
                ->where('is_static', true)
                ->first();
        }

        if (!$thumbnail) {
            $thumbnail = $this->thumbnails()
                ->where('default_for_produk', true)
                ->first();
        }

        return $thumbnail;
    }
}
