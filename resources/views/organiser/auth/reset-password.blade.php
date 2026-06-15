@extends('layouts.app')
@section('title', 'Set New Password')
@section('content')
<div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-extrabold text-white">Set New Password</h1>
      <p class="text-gray-400 text-sm mt-2">Choose a strong password for your account.</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
      @if($errors->any())
      <div class="bg-red-900/40 border border-red-700/50 rounded-xl p-3 mb-5">
        @foreach($errors->all() as $e)<div class="text-red-300 text-sm">{{ $e }}</div>@endforeach
      </div>
      @endif
      <form action="{{ route('organiser.password.reset') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">New Password</label>
          <input type="password" name="password" required minlength="8" maxlength="15"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Min. 8 characters">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Confirm New Password</label>
          <input type="password" name="password_confirmation" required maxlength="15"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Repeat password">
        </div>
        <button type="submit" class="w-full py-3.5 font-extrabold text-white rounded-xl text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          Reset Password →
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
