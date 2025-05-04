<?php

namespace App\Console\Commands;

use App\Models\WebConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportTailwindColors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tailwind:export-colors';
    protected $description = 'Export Tailwind colors from web config to a JS file';

    public function handle()
    {
        $colors = [
            // Warna utama
            'primary' => [
                'DEFAULT' => WebConfig::get('primary_color', '#9b87f5'),
                'hover' => WebConfig::get('primary_hover', '#a897f6'),
                'text' => WebConfig::get('text_primary', '#ffffff'),
            ],
            'secondary' => [
                'DEFAULT' => WebConfig::get('secondary_color', '#33C3F0'),
                'hover' => WebConfig::get('secondary_hover', '#5ed4f5'),
                'text' => WebConfig::get('text_secondary', '#ffffff'),
            ],

            // Warna untuk halaman user
            'header_background' => WebConfig::get('header_bg', '#000000'),
            'footer_background' => WebConfig::get('footer_bg', '#000000'),
            'content_background' => WebConfig::get('content_bg', '#000000'),

            // Warna admin panel
            'dark' => [
                'DEFAULT' => '#070c16',
                'lighter' => '#111927',
                'card' => '#101826',
                'sidebar' => '#111822',
            ]
        ];

        $content = 'module.exports = ' . json_encode($colors, JSON_PRETTY_PRINT) . ';';

        File::put(resource_path('js/tailwind.dynamic.colors.js'), $content);

        $this->info('✅ Tailwind colors exported to tailwind.dynamic.colors.js');
    }
}
