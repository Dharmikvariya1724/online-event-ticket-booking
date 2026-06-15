@extends('layouts.organiser')
@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Manage your account details')

@section('content')
<div class="max-w-3xl">

  {{-- Profile Card --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-5">
    <div class="h-28 rounded-t-2xl flex items-start justify-end p-4"
        style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#db2777)">

        <!-- Theme Toggle -->
        <button type="button"
    data-theme-toggle
    class="flex items-center gap-2 text-xs font-semibold text-white bg-black/20 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/20 hover:bg-black/30">

    <span data-theme-icon>🌙</span>
    <span data-theme-label>Dark</span>

</button>

    </div>
    <div class="px-6 pb-6">
      
      <div class="flex items-end justify-between -mt-10 mb-5">
        @if($organiser->logo_url)
        <img src="{{ $organiser->logo_url }}" alt="" class="w-20 h-20 rounded-2xl border-4 border-gray-900 object-cover">
        @else
        <div class="w-20 h-20 rounded-2xl border-4 border-gray-900 flex items-center justify-center text-2xl font-extrabold text-white" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">{{ $organiser->initials }}</div>
        @endif
        <a href="{{ route('organiser.profile.edit') }}" class="text-xs font-semibold text-white px-4 py-2 rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Edit Profile</a>
      </div>
      <h2 class="text-xl font-extrabold text-white">{{ $organiser->company_name }}</h2>
      <p class="text-gray-400 text-sm">{{ $organiser->name }}</p>
      @if($organiser->bio)<p class="text-gray-300 text-sm mt-3 leading-relaxed">{{ $organiser->bio }}</p>@endif
    </div>
    
  </div>

  {{-- Details Grid --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
    <h3 class="font-bold text-white mb-4">Account Information</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @foreach([
        ['Email', $organiser->email, '✉️'],
        ['Phone', $organiser->phone ?? 'Not set', '📱'],
        ['Website', $organiser->website ?? 'Not set', '🌐'],
        ['Account Status', $organiser->is_approved ? 'Approved ✓' : 'Pending', '🔐'],
        ['Member Since', ticketly_format_date($organiser->created_at), '📅'],
        ['Last Active', $organiser->last_active_at?->diffForHumans() ?? 'Never', '🕐'],
      ] as [$label, $value, $icon])
      <div class="bg-gray-800/50 rounded-xl p-4">
        <div class="text-xs text-gray-500 mb-1">{{ $icon }} {{ $label }}</div>
        <div class="text-sm font-semibold text-white">{{ $value }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Stripe Connect --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <h3 class="font-bold text-white">Stripe Connect</h3>
        <p class="text-sm text-gray-400 mt-1">Receive ticket payouts directly to your bank account.</p>
      </div>
      @if(!$organiser->stripe_account_id || !$organiser->stripe_onboarding_complete)
        <a href="{{ route('organiser.stripe.connect') }}"
           class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700">
          Connect with Stripe
        </a>
      @endif
    </div>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
      <div class="bg-gray-800/50 rounded-xl p-3">
        <div class="text-xs text-gray-500 mb-1">Status</div>
        <div class="font-semibold text-white">
          {{ $organiser->stripe_account_id ? 'Connected' : 'Not Connected' }}
        </div>
      </div>
      <div class="bg-gray-800/50 rounded-xl p-3">
        <div class="text-xs text-gray-500 mb-1">Account ID</div>
        <div class="font-semibold text-white">
          {{ $organiser->stripe_account_id ?? '—' }}
        </div>
      </div>
      <div class="bg-gray-800/50 rounded-xl p-3">
        <div class="text-xs text-gray-500 mb-1">Onboarding</div>
        <div class="font-semibold text-white">
          {{ $organiser->stripe_onboarding_complete ? 'Complete' : 'Incomplete' }}
        </div>
      </div>
    </div>
  </div>

  {{-- Change Password --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-5">
    <h3 class="font-bold text-white mb-4">Change Password</h3>
    <form action="{{ route('organiser.profile.password') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Current Password</label>
          <input type="password" name="current_password" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">New Password</label>
          <input type="password" name="password" required minlength="8" maxlength="15" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Confirm New</label>
          <input type="password" name="password_confirmation" required maxlength="15" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>
      <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white rounded-xl bg-indigo-600 hover:bg-indigo-700 transition-colors">Update Password</button>
    </form>
  </div>

  {{-- Danger Zone --}}
  <div class="bg-gray-900 border border-red-900/50 rounded-2xl p-6">
    <h3 class="font-bold text-red-400 mb-2">Danger Zone</h3>
    <p class="text-gray-400 text-sm mb-4">Permanently delete your account. This action cannot be undone. You cannot delete your account if you have active upcoming events.</p>

    @error('delete')<div class="bg-red-900/40 border border-red-700/50 rounded-xl p-3 mb-4 text-red-300 text-sm">{{ $message }}</div>@enderror

    <form action="{{ route('organiser.profile.destroy') }}" method="POST" data-confirm="Are you absolutely sure? This will permanently delete your account." data-confirm-ok="Delete account">
      @csrf @method('DELETE')
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" name="confirm_delete" placeholder='Type DELETE to confirm'
               class="w-full bg-gray-800 border border-red-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500 sm:w-52" required>
        <button type="submit" class="inline-flex w-full items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-red-700 hover:bg-red-600 rounded-xl transition-colors sm:w-auto" style="color:#ffffff !important;">Delete Account</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.querySelector("[data-theme-toggle]");
    const label = document.querySelector("[data-theme-label]");
    const icon = document.querySelector("[data-theme-icon]");

    if(!toggle) return;

    function setTheme(theme){
        if(theme === "dark"){
            document.documentElement.classList.add("dark");
            icon.textContent = "☀️";
            label.textContent = "Light";
        }else{
            document.documentElement.classList.remove("dark");
            icon.textContent = "🌙";
            label.textContent = "Dark";
        }
        localStorage.setItem("theme", theme);
    }

    const savedTheme = localStorage.getItem("theme") || "dark";
    setTheme(savedTheme);

    toggle.addEventListener("click", function(){
        const isDark = document.documentElement.classList.contains("dark");
        setTheme(isDark ? "light" : "dark");
    });

});
</script>
@endsection
