<?php

namespace Tests\Unit;

use App\Http\Controllers\Organiser\ProfileController;
use App\Models\Organiser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserProfilePasswordTest extends TestCase
{
    public function test_it_rejects_using_the_current_password_as_the_new_password(): void
    {
        $organiser = new Organiser([
            'name' => 'Test Organiser',
            'company_name' => 'Test Company',
            'email' => 'organiser@example.com',
            'password' => Hash::make('SamePass@123'),
        ]);

        $request = Request::create('/organiser/profile/password', 'POST', [
            'current_password' => 'SamePass@123',
            'password' => 'SamePass@123',
            'password_confirmation' => 'SamePass@123',
        ]);

        $session = $this->app['session.store'];
        $session->start();
        $request->setLaravelSession($session);
        $request->attributes->set('organiser', $organiser);

        $response = app(ProfileController::class)->updatePassword($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($session->has('errors'));
        $this->assertSame(
            'You cannot use a previously used password. Please Try with another password.',
            $session->get('errors')->getBag('default')->first('password')
        );
    }
}
