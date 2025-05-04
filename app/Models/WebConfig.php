<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebConfig extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Define allowed color variables
    const ALLOWED_COLORS = [
        'primary_color',
        'primary_hover',
        'secondary_color',
        'secondary_hover',
        'content_bg',
        'footer_bg',
        'header_bg',
        'text_primary',
        'text_secondary',
    ];

    // Default values for settings
    const DEFAULT_VALUES = [
        'judul_web' => 'Game Top-Up Website',
        'meta_title' => 'Game Top-Up | Fast & Secure',
        'meta_description' => 'The best place to buy game credits and top-ups at affordable prices.',
        'meta_keywords' => 'game top-up, mobile legends, free fire, pubg mobile',
        'support_instagram' => 'https://instagram.com/yourgame',
        'support_whatsapp' => '6281234567890',
        'support_email' => 'support@yourgame.com',
        'support_youtube' => 'https://youtube.com/yourgame',
        'support_facebook' => 'https://facebook.com/yourgame',
        'primary_color' => '#6366F1',
        'primary_hover' => '#818CF8',
        'secondary_color' => '#8B5CF6',
        'secondary_hover' => '#A78BFA',
        'content_bg' => '#1F2937',
        'footer_bg' => '#111827',
        'header_bg' => '#111827',
        'text_primary' => '#F9FAFB',
        'text_secondary' => '#E5E7EB',
        'logo_header' => null,
        'logo_footer' => null,
        'logo_favicon' => null,
    ];

    public static function get($key, $default = null)
    {
        $config = self::where('key', $key)->first();

        if (!$config) {
            return $default ?? self::DEFAULT_VALUES[$key] ?? null;
        }

        return $config->value;
    }

    public static function set($key, $value, $description = null, $type = 'text')
    {
        $config = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type
            ]
        );

        return $config;
    }

    public static function getColorPaletteAttribute()
    {
        $colorPalette = [];

        foreach (self::ALLOWED_COLORS as $color) {
            $colorPalette[$color] = self::get($color, self::DEFAULT_VALUES[$color] ?? '#000000');
        }

        return $colorPalette;
    }

    public function getSocialLinksAttribute()
    {
        return [
            'instagram' => self::get('support_instagram'),
            'whatsapp' => self::get('support_whatsapp'),
            'email' => self::get('support_email'),
            'youtube' => self::get('support_youtube'),
            'facebook' => self::get('support_facebook'),
        ];
    }

    // Method to validate hex color
    public static function isValidHexColor($color)
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }
}
