<?php

namespace Tests\Feature;

use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganiserRegistrationPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        Mail::fake();
    }

    public function test_registration_accepts_valid_11_digit_phone_starting_with_07(): void
    {
        $response = $this->post('/organiser/register', [
            'name' => 'Valid Organiser',
            'company_name' => 'Valid Co',
            'email' => 'valid.organiser@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '07123456789',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('organisers', [
            'email' => 'valid.organiser@gmail.com',
            'phone' => '07123456789',
        ]);
    }

    public function test_registration_rejects_phone_without_07_prefix(): void
    {
        $response = $this->post('/organiser/register', [
            'name' => 'Prefix Organiser',
            'company_name' => 'Prefix Co',
            'email' => 'prefix.organiser@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '08123456789',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('Phone number must start with 07.', session('errors')->get('phone')[0]);
    }

    public function test_registration_rejects_phone_with_non_numeric_characters(): void
    {
        $response = $this->post('/organiser/register', [
            'name' => 'Format Organiser',
            'company_name' => 'Format Co',
            'email' => 'format.organiser@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '07abc123456',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('Phone number must be exactly 11 digits and contain numbers only.', session('errors')->get('phone')[0]);
    }

    public function test_registration_rejects_phone_with_spaces(): void
    {
        $response = $this->post('/organiser/register', [
            'name' => 'Space Organiser',
            'company_name' => 'Space Co',
            'email' => 'space.organiser@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '07 123456789',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('Phone number must be exactly 11 digits and contain numbers only.', session('errors')->get('phone')[0]);
    }

    public function test_registration_blocks_duplicate_phone(): void
    {
        Organiser::create([
            'name' => 'Existing Organiser',
            'company_name' => 'Existing Co',
            'email' => 'existing.organiser@gmail.com',
            'password' => bcrypt('password123'),
            'phone' => '07123456789',
        ]);

        $response = $this->post('/organiser/register', [
            'name' => 'Duplicate Organiser',
            'company_name' => 'Duplicate Co',
            'email' => 'duplicate.organiser@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '07123456789',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('This phone number is already registered.', session('errors')->get('phone')[0]);
    }
}
