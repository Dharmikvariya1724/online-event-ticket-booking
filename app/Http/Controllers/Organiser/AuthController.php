<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Organiser;
use App\Models\SystemSetting;
use App\Models\EmailLog;
use App\Support\AdminAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\AdminNewOrganiser;

class AuthController extends Controller
{
    private const SESSION_KEY     = 'organiser_id';
    private const AUTH_STATE_KEY  = 'organiser_auth_state';
    private const INACTIVITY_MINS = 60; // Session expires after 60 minutes of inactivity

    // ── Register ──────────────────────────────────────────────────
    public function showRegister()
    {
        if (session(self::SESSION_KEY)) return redirect()->route('organiser.dashboard');
        return view('organiser.auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'phone' => trim((string) $request->input('phone')),
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $validated = $request->validate([
            'name'         => 'required|string|max:40',
            'company_name' => 'nullable|string|max:50',
            'email' => 'required|email:rfc,dns|max:100|unique:organisers,email',
            'password'     => 'required|string|min:8|max:15|confirmed',
            'phone'        => ['bail', 'required', 'digits:11', 'starts_with:07', 'unique:organisers,phone'],
        ], [
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 11 digits and contain numbers only.',
            'phone.starts_with' => 'Phone number must start with 07.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        $organiser = Organiser::create([
            'name'         => $validated['name'],
            'company_name' => $validated['company_name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'phone'        => $validated['phone'] ?? null,
            'is_approved'  => false,
        ]);

        Log::info('[Organiser] New registration: ' . $organiser->email);

        try {
            Mail::send('emails.organiser-registration-pending', [
                'organiser' => $organiser,
            ], function ($message) use ($organiser) {
                $message->to($organiser->email)
                    ->subject('Your Ticketly organiser registration is pending approval');
            });
            EmailLog::logSent($organiser->email, 'Organiser registration pending', 'organiser_registration_pending', $organiser);
        } catch (\Exception $e) {
            Log::error('[Organiser] Registration confirmation email failed: ' . $e->getMessage(), [
                'organiser_id' => $organiser->id,
                'email'        => $organiser->email,
            ]);
            EmailLog::logFailed($organiser->email, 'Organiser registration pending', $e->getMessage(), 'organiser_registration_pending', $organiser);
        }

        $adminEmail = null;
        try {
            $adminEmail = SystemSetting::getValue('admin_email', config('ticketly.support_email', 'support@ticketly.com'));
            if (!empty($adminEmail)) {
                Mail::to($adminEmail)->send(new AdminNewOrganiser($organiser));
                EmailLog::logSent($adminEmail, 'New organiser registration', 'admin_new_organiser', $organiser);
            }
        } catch (\Exception $e) {
            Log::error('[Organiser] Admin notification email failed: ' . $e->getMessage(), [
                'organiser_id' => $organiser->id,
            ]);
            if (!empty($adminEmail)) {
                EmailLog::logFailed($adminEmail, 'New organiser registration', $e->getMessage(), 'admin_new_organiser', $organiser);
            }
        }

        return view('organiser.auth.pending');
    }

    // ── Login ─────────────────────────────────────────────────────
    public function showLogin()
    {
        if (AdminAuth::user()) return redirect()->route('admin.dashboard');
        if ($this->getAuthenticatedOrganiser()) return redirect()->route('organiser.dashboard');
        return view('organiser.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();
        if ($admin && Hash::check($validated['password'], $admin->password)) {
            $request->session()->forget([self::SESSION_KEY, 'organiser_last_active']);
            $request->session()->regenerate();
            AdminAuth::login($admin);
            return redirect()->route('admin.dashboard');
        }

        $organiser = Organiser::where('email', $validated['email'])->first();

        if (!$organiser || !Hash::check($validated['password'], $organiser->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        if ($organiser->isSuspended()) {
            return back()->withErrors(['email' => 'This organiser account is suspended. Please contact support.'])->withInput();
        }

        if ($organiser->rejected_at) {
            return back()->withErrors(['email' => 'Your organiser account was rejected. Please contact support.'])->withInput();
        }

        if (!$organiser->isApproved()) {
            return redirect()->route('organiser.pending');
        }

        $request->session()->regenerate();

        session([
            self::SESSION_KEY              => $organiser->id,
            self::AUTH_STATE_KEY           => (string) Str::uuid(),
            'organiser_last_active'        => now()->timestamp,
        ]);

        $organiser->touchActivity();

        return redirect()->route('organiser.dashboard');
    }

    // ── Logout ────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->session()->forget([self::SESSION_KEY, self::AUTH_STATE_KEY, 'organiser_last_active']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect()->route('organiser.login')->with('info', 'You have been logged out.');
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }

    // ── Pending Approval ──────────────────────────────────────────
    public function pending()
    {
        return view('organiser.auth.pending');
    }

    // ── Forgot Password ───────────────────────────────────────────
    public function showForgotPassword()
    {
        return view('organiser.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $organiser = Organiser::where('email', $request->email)->first();

        if ($organiser) {
            $token     = $organiser->generatePasswordResetToken();
            $resetLink = route('organiser.password.reset.form', ['token' => $token, 'email' => $organiser->email]);

            try {
                Mail::send('emails.organiser-password-reset', [
                    'organiser' => $organiser,
                    'resetLink' => $resetLink,
                ], function ($message) use ($organiser) {
                    $message->to($organiser->email)
                        ->subject('🔐 Reset Your Ticketly Organiser Password');
                });
                EmailLog::logSent($organiser->email, 'Organiser password reset', 'organiser_password_reset', $organiser);
            } catch (\Exception $e) {
                Log::error('[Organiser] Password reset email failed: ' . $e->getMessage());
                EmailLog::logFailed($organiser->email, 'Organiser password reset', $e->getMessage(), 'organiser_password_reset', $organiser);
                return back()->with('error', 'Email failed to send. Check logs.');
            }
        }

        // Always show success to prevent email enumeration
        return back()->with('success', 'If an account exists with that email, a reset link has been sent. It expires in 24 hours.');
    }

    public function showResetForm(Request $request)
    {
        return view('organiser.auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|max:15|confirmed',
        ]);

        $organiser = Organiser::where('email', $validated['email'])->first();

        if (!$organiser || !$organiser->isResetTokenValid($validated['token'])) {
            return back()->withErrors(['token' => 'This password reset link is invalid or has expired.']);
        }

        if (Hash::check($validated['password'], $organiser->password)) {
            return back()
                ->withErrors(['password' => 'You cannot use a previously used password. Please Try with another password.'])
                ->withInput($request->only('email', 'token'));
        }

        $organiser->update(['password' => Hash::make($validated['password'])]);
        $organiser->clearResetToken();

        return redirect()->route('organiser.login')->with('success', 'Password reset successfully. You can now log in.');
    }

    // ── Auth Helper ───────────────────────────────────────────────
    public static function getAuthenticatedOrganiser(): ?Organiser
    {
        $id           = session(self::SESSION_KEY);
        $lastActive   = session('organiser_last_active');

        if (!$id) return null;

        // Inactivity check
        if ($lastActive && (now()->timestamp - $lastActive) > (self::INACTIVITY_MINS * 60)) {
            session()->forget([self::SESSION_KEY, 'organiser_last_active']);
            return null;
        }

        $organiser = Organiser::find($id);
        if (!$organiser || !$organiser->isApproved() || $organiser->isSuspended()) {
            session()->forget([self::SESSION_KEY, 'organiser_last_active']);
            return null;
        }

        if (!session(self::AUTH_STATE_KEY)) {
            session([self::AUTH_STATE_KEY => (string) Str::uuid()]);
        }

        // Refresh activity timestamp
        session(['organiser_last_active' => now()->timestamp]);
        $organiser->touchActivity();

        return $organiser;
    }
}
