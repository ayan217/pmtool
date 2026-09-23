<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_pages_include_pwa_and_mobile_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('apple-mobile-web-app-capable', false)
            ->assertSee('mobile-tabbar', false)
            ->assertSee('appOffcanvas', false)
            ->assertSee('>Home</span>', false)
            ->assertSee('>Calendar</span>', false);
    }

    public function test_login_includes_pwa_meta(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('apple-mobile-web-app-capable', false);
    }

    public function test_calendar_uses_a_mobile_friendly_shell(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('calendar.index'))
            ->assertOk()
            ->assertSee('calendar-shell', false)
            ->assertSee('listWeek', false);
    }

    public function test_pwa_assets_exist(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
        $this->assertFileExists(public_path('icons/icon-512-maskable.png'));
        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));
    }
}
