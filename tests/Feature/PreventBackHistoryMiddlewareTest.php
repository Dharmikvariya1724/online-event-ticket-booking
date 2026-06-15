<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PreventBackHistoryMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function makeOrganiser(): Organiser
    {
        return Organiser::create([
            'name' => 'Protected Organiser',
            'company_name' => 'Protected Co',
            'email' => 'protected.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ]);
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'admin_last_active' => now()->timestamp,
        ];
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    private function assertNoCacheHeaders($response): void
    {
        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function test_admin_protected_pages_include_no_cache_headers(): void
    {
        $admin = $this->makeAdmin();

        $response = $this
            ->withSession($this->adminSession($admin))
            ->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('ticketly-global-loader', false);
        $response->assertSee("html[data-protected-history-hidden='1'][data-ticket-loader-visible='1'] body", false);
        $this->assertNoCacheHeaders($response);
    }

    public function test_organiser_protected_pages_include_no_cache_headers(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.profile.show'));

        $response->assertOk();
        $response->assertSee('ticketly-global-loader', false);
        $response->assertSee("html[data-protected-history-hidden='1'][data-ticket-loader-visible='1'] body", false);
        $this->assertNoCacheHeaders($response);
    }

    public function test_admin_protected_redirects_to_login_with_no_cache_headers_when_session_is_missing(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('admin.login'));
        $this->assertNoCacheHeaders($response);
    }

    public function test_organiser_protected_redirects_to_login_with_no_cache_headers_when_session_is_missing(): void
    {
        $response = $this->get(route('organiser.profile.show'));

        $response->assertRedirect(route('organiser.login'));
        $this->assertNoCacheHeaders($response);
    }

    public function test_login_pages_render_logout_history_guard_script(): void
    {
        $organiserLoginResponse = $this->get(route('organiser.login'));
        $organiserLoginResponse->assertOk();
        $organiserLoginResponse->assertSee('var namespace = "organiser";', false);
        $organiserLoginResponse->assertSee("var logoutStateKey = 'ticketly:' + namespace + ':logout-state';", false);

        $adminLoginResponse = $this->get(route('admin.login'));
        $adminLoginResponse->assertOk();
        $adminLoginResponse->assertSee('var namespace = "admin";', false);
        $adminLoginResponse->assertSee("var logoutStateKey = 'ticketly:' + namespace + ':logout-state';", false);
    }

    public function test_logout_responses_are_not_cacheable(): void
    {
        $organiser = $this->makeOrganiser();
        $token = 'logout-token';

        $organiserResponse = $this
            ->withSession(array_merge($this->organiserSession($organiser), ['organiser_auth_state' => 'organiser-state', '_token' => $token]))
            ->post(route('organiser.logout'), ['_token' => $token]);

        $organiserResponse->assertRedirect(route('organiser.login'));
        $this->assertNoCacheHeaders($organiserResponse);

        $admin = $this->makeAdmin();
        $adminToken = 'admin-logout-token';

        $adminResponse = $this
            ->withSession(array_merge($this->adminSession($admin), ['admin_auth_state' => 'admin-state', '_token' => $adminToken]))
            ->post(route('admin.logout'), ['_token' => $adminToken]);

        $adminResponse->assertRedirect(route('admin.login'));
        $this->assertNoCacheHeaders($adminResponse);
    }
}
