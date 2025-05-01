<?php

namespace App\Models;

use App\Models\Produk;
use App\Models\ProdukInputOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukInputField extends Model
{
    protected $guarded = [
        'id'
    ];

    public function options(): HasMany
    {
        return $this->hasMany(ProdukInputOption::class, 'produk_input_field_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
