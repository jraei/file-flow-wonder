<?php

namespace App\Http\Middleware;

use App\Models\WebConfig;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'role' => $request->user() ? $request->user()->role->role_name : null
            ],
            'flash' => [
                'status' => session('status')
            ],
            'web_details' => [
                'logo_header' => WebConfig::where('key', 'logo_header')->first()->value ?? null,
                'logo_footer' => WebConfig::where('key', 'logo_footer')->first()->value ?? null,
                'judul_web' => WebConfig::where('key', 'judul_web')->first()->value ?? 'Store Top Up',
                'meta_description' => WebConfig::where('key', 'meta_description')->first()->value ?? 'The best place to buy game credits and top-ups at affordable prices.',
                'meta_title' => WebConfig::where('key', 'meta_title')->first()->value ?? 'The best place to buy game credits and top-ups at affordable prices.',
                'support_instagram' => WebConfig::where('key', 'support_instagram')->first()->value ?? null,
                'support_whatsapp' => WebConfig::where('key', 'support_whatsapp')->first()->value ?? null,
                'support_email' => WebConfig::where('key', 'support_email')->first()->value ?? null,
                'support_youtube' => WebConfig::where('key', 'support_youtube')->first()->value ?? null,
                'support_facebook' => WebConfig::where('key', 'support_facebook')->first()->value ?? null
            ]
        ];
    }
}
