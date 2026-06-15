<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        return view('organiser.profile.show', compact('organiser'));
    }

    public function edit(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        return view('organiser.profile.edit', compact('organiser'));
    }

    public function update(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        $request->merge([
            'phone' => trim((string) $request->input('phone')),
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email'        => 'required|email|unique:organisers,email,' . $organiser->id,
            'phone'        => 'bail|required|digits:11|starts_with:07|unique:organisers,phone,' . $organiser->id,
            'website'      => 'nullable|url|max:255',
            'bio'          => 'nullable|string|max:2000',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 11 digits and contain numbers only.',
            'phone.starts_with' => 'Phone number must start with 07.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($organiser->logo) {
                $this->deleteLogoFile($organiser->logo);
            }
            $validated['logo'] = $this->storeLogoFile($request->file('logo'));
        }

        $organiser->update($validated);

        Log::info('[Organiser] Profile updated: ' . $organiser->email);

        return redirect()->route('organiser.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|max:15|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $organiser->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        if (Hash::check($validated['password'], $organiser->password)) {
            return back()->withErrors(['password' => 'You cannot use a previously used password. Please Try with another password.']);
        }

        $organiser->update(['password' => Hash::make($validated['password'])]);

        Log::info('[Organiser] Password changed: ' . $organiser->email);

        return back()->with('success', 'Password updated successfully.');
    }

    public function destroy(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        // Cannot delete if has active events
        if ($organiser->hasActiveEvents()) {
            return back()->withErrors([
                'delete' => 'You cannot delete your account while you have active upcoming events. Please cancel or complete all events first.'
            ]);
        }

        $request->validate(['confirm_delete' => 'required|in:DELETE']);

        Log::info('[Organiser] Account deleted: ' . $organiser->email);

        if ($organiser->logo) {
            $this->deleteLogoFile($organiser->logo);
        }

        $organiser->delete();
        $request->session()->flush();

        return redirect()->route('home')->with('info', 'Your organiser account has been permanently deleted.');
    }

    private function storeLogoFile(\Illuminate\Http\UploadedFile $file): string
    {
        $directory = $this->getUploadsRoot() . DIRECTORY_SEPARATOR . 'organisers';
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/organisers/' . $filename;
    }

    private function deleteLogoFile(?string $logo): void
    {
        if (!$logo) {
            return;
        }

        $path = $this->resolveLogoPath($logo);
        if ($path && File::exists($path)) {
            File::delete($path);
        }
    }

    private function resolveLogoPath(string $logo): ?string
    {
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return null;
        }

        if (str_starts_with($logo, 'uploads/')) {
            $basePath = base_path($logo);
            if (File::exists($basePath)) {
                return $basePath;
            }

            return public_path($logo);
        }

        $fallback = 'uploads/organisers/' . basename($logo);
        $basePath = base_path($fallback);
        if (File::exists($basePath)) {
            return $basePath;
        }

        return public_path($fallback);
    }

    private function getUploadsRoot(): string
    {
        $baseUploads = base_path('uploads');
        if (File::exists($baseUploads)) {
            return $baseUploads;
        }

        return public_path('uploads');
    }
}
