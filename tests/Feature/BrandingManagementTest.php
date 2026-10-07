<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_branding_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.branding.edit'))
            ->assertOk()
            ->assertSee('Company branding')
            ->assertSee('name="logo"', false)
            ->assertSee('name="favicon"', false);
    }

    public function test_admin_cannot_access_branding_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.branding.edit'))
            ->assertForbidden();
    }

    public function test_superadmin_can_update_branding_with_uploads(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->superAdmin()->create();
        CompanySetting::current();

        $logo = UploadedFile::fake()->image('logo.png', 200, 80);
        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        $response = $this->actingAs($superAdmin)->put(route('admin.branding.update'), [
            'company_name' => 'Acme HR',
            'tagline' => 'People first',
            'support_email' => 'help@acme.test',
            'primary_color' => '#123456',
            'logo' => $logo,
            'favicon' => $favicon,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $settings = CompanySetting::current()->fresh();

        $this->assertSame('Acme HR', $settings->company_name);
        $this->assertSame('People first', $settings->tagline);
        $this->assertSame('help@acme.test', $settings->support_email);
        $this->assertSame('#123456', $settings->primary_color);
        $this->assertNotNull($settings->logo_path);
        $this->assertNotNull($settings->favicon_path);
        Storage::disk('public')->assertExists($settings->logo_path);
        Storage::disk('public')->assertExists($settings->favicon_path);
    }

    public function test_branding_validation_rejects_invalid_color(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->from(route('admin.branding.edit'))
            ->put(route('admin.branding.update'), [
                'company_name' => 'Acme HR',
                'primary_color' => 'blue',
            ])
            ->assertRedirect(route('admin.branding.edit'))
            ->assertSessionHasErrors('primary_color');
    }

    public function test_guest_layouts_show_branded_company_name(): void
    {
        CompanySetting::current()->update([
            'company_name' => 'Branded Co',
            'tagline' => 'Clock in with ease',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Branded Co')
            ->assertSee('Clock in with ease');
    }
}
