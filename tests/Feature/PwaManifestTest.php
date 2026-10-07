<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_available(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('start_url', '/')
            ->assertJsonStructure([
                'name',
                'short_name',
                'icons',
                'theme_color',
            ]);
    }

    public function test_service_worker_is_available(): void
    {
        $this->get(route('pwa.service-worker'))
            ->assertOk()
            ->assertHeader('content-type', 'application/javascript; charset=utf-8')
            ->assertSee('CACHE_VERSION', false)
            ->assertSee('OFFLINE_URL', false);
    }

    public function test_offline_page_is_available(): void
    {
        $this->get(route('pwa.offline'))
            ->assertOk()
            ->assertSee(__('You are offline'));
    }

    public function test_layouts_include_manifest_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee(route('pwa.manifest'), false);
    }
}
