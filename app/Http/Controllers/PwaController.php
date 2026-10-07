<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $branding = $this->resolveCompanyBranding();
        $name = $branding?->company_name ?: config('app.name', 'Attendance HR');
        $themeColor = $branding?->primary_color ?: '#4f46e5';

        return response()->json([
            'name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'description' => $branding?->tagline ?: __('Attendance & HR'),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#f1f5f9',
            'theme_color' => $themeColor,
            'icons' => [
                [
                    'src' => asset('icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    public function offline(): View
    {
        return view('pwa.offline');
    }

    public function serviceWorker(): Response
    {
        $cacheVersion = 'v1';
        $manifestPath = public_path('build/manifest.json');

        if (is_file($manifestPath)) {
            $cacheVersion = 'v'.substr(md5_file($manifestPath) ?: '1', 0, 10);
        }

        $script = view('pwa.service-worker', [
            'cacheVersion' => $cacheVersion,
            'offlineUrl' => url('/offline'),
        ])->render();

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function resolveCompanyBranding(): ?CompanySetting
    {
        try {
            if (! Schema::hasTable('company_settings')) {
                return null;
            }

            return CompanySetting::current();
        } catch (Throwable) {
            return null;
        }
    }
}
