<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Layanan;
use App\Models\FlashsaleItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashsaleEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
    ];

    public function item(): HasMany
    {
        return $this->hasMany(FlashsaleItem::class);
    }

    public function layanan(): BelongsToMany
    {
        return $this->belongsToMany(Layanan::class, 'flashsale_items')
            ->withPivot('harga_flashsale', 'stok', 'batas_user', 'status')
            ->withTimestamps();
    }

    // Scope for active events (currently running)
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'active')
            ->where('event_start_date', '<=', $now)
            ->where('event_end_date', '>=', $now);
    }

    // Scope for upcoming events
    public function scopeUpcoming($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'active')
            ->where('event_start_date', '>', $now);
    }

    // Check if the event is currently active
    public function isActive()
    {
        $now = Carbon::now();
        return $this->status === 'active' &&
            $this->event_start_date <= $now &&
            $this->event_end_date >= $now;
    }
}
