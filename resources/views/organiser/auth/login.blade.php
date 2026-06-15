@extends('layouts.app')
@php
  $loginTitle = 'Sign In';
  $loginSubtitle = 'Access your event management dashboard';
@endphp
@section('title', 'Sign In')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        </div>
        <span class="text-xl font-extrabold text-white">Ticketly</span>
      </a>
      <h1 class="text-2xl font-extrabold text-white mt-4">{{ $loginTitle }}</h1>
      <p class="text-gray-400 text-sm mt-1">{{ $loginSubtitle }}</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">

      @if(session('success'))
      <div class="bg-emerald-500 border border-emerald-700/50 rounded-xl p-3 mb-5 text-emerald-300 text-sm" style="color:#ffffff !important;">{{ session('success') }}</div>
      @endif
      @if(session('info'))
      <div class="bg-blue-500 border border-blue-700/50 rounded-xl p-3 mb-5 text-blue-300 text-sm" style="color:#ffffff !important;">{{ session('info') }}</div>
      @endif

      @if($errors->any())
      <div class="bg-red-500 border border-red-700/50 rounded-xl p-3 mb-5">
        @foreach($errors->all() as $e)<div class="text-red-300 text-sm" style="color:#ffffff !important;">{{ $e }}</div>@endforeach
      </div>
      @endif

      <form action="{{ $loginAction ?? route('organiser.login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Email Address</label>
          <input type="email" name="email" value="{{ old('email') }}" required
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@company.com" autofocus>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Password</label>
              <a href="{{ route('organiser.password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Forgot password?</a>
          </div>
          <input type="password" name="password" required
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••">
        </div>

        <button type="submit" class="w-full py-3.5 font-extrabold text-white rounded-xl text-sm transition-all" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          Sign In →
        </button>

        <p class="text-center text-xs text-gray-500">Don't have an account? <a href="{{ route('organiser.register') }}" class="text-indigo-400 hover:text-indigo-300">Register</a></p>
      </form>
    </div>
  </div>
</div>
@endsection
