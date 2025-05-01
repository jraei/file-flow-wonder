<?php

namespace App\Models;

use App\Models\ProdukInputField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukInputOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inputField(): BelongsTo
    {
        return $this->belongsTo(ProdukInputField::class, 'produk_input_field_id');
    }
}
