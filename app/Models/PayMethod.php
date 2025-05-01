<?php

namespace App\Models;

use App\Models\Deposit;
use App\Models\PaymentProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayMethod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider', 'provider_name');
    }

    public function deposit(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
}