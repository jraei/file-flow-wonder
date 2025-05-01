
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembelian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'callback_data' => 'json',
        'contact_info' => 'json', // Cast the contact_info field as JSON
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'order_id', 'order_id');
    }

    public static function generateReferenceId()
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $uniqueId = $prefix . $date . $random;

        // Check if ID already exists
        while (self::where('reference_id', $uniqueId)->exists()) {
            $random = rand(1000, 9999);
            $uniqueId = $prefix . $date . $random;
        }

        return $uniqueId;
    }
    
    /**
     * Get formatted contact phone number with international code
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->contact_info) {
            return null;
        }
        
        $contactInfo = is_string($this->contact_info) ? json_decode($this->contact_info, true) : $this->contact_info;
        
        if (isset($contactInfo['phone'])) {
            // Return phone as is if it already has a + prefix (international format)
            if (str_starts_with($contactInfo['phone'], '+')) {
                return $contactInfo['phone'];
            }
            
            // Format with country code if available
            if (isset($contactInfo['country_code'])) {
                $countryCodes = [
                    'ID' => '62', // Indonesia
                    'MY' => '60', // Malaysia
                    'SG' => '65', // Singapore
                    'US' => '1',  // United States
                    // Add more as needed
                ];
                
                $countryCode = $countryCodes[$contactInfo['country_code']] ?? '62'; // Default to Indonesia
                $phone = $contactInfo['phone'];
                
                // Remove leading zeros
                $phone = ltrim($phone, '0');
                
                return "+{$countryCode}{$phone}";
            }
            
            // Default to Indonesia code if no country code is specified
            $phone = ltrim($contactInfo['phone'], '0');
            return "+62{$phone}";
        }
        
        return null;
    }
}
