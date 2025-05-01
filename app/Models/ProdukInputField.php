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

    protected $casts = [
        'required' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(ProdukInputOption::class, 'produk_input_field_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Check if this input field is for the user ID
     * 
     * @return bool
     */
    public function isUserIdField(): bool
    {
        return $this->name === 'user_id' || $this->name === 'player_id';
    }

    /**
     * Check if this input field is for the server/zone ID
     * 
     * @return bool
     */
    public function isServerField(): bool
    {
        return $this->name === 'server_id' || $this->name === 'zone_id';
    }
}