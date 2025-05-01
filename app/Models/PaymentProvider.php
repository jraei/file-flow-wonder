<?php

namespace App\Models;

use App\Models\PayMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentProvider extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function payMethods(): HasMany
    {
        return $this->hasMany(PayMethod::class, 'payment_provider', 'provider_name');
    }
}
