@extends('layouts.app')
@section('title', 'Forgot Password')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-extrabold text-white">Reset Your Password</h1>
      <p class="text-gray-400 text-sm mt-2">Enter your email address and we'll send a reset link valid for 24 hours.</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
      @if(session('success'))
      <div class="bg-emerald-900/40 border border-emerald-700/50 rounded-xl p-3 mb-5 text-emerald-300 text-sm">{{ session('success') }}</div>
      @endif
      @if($errors->any())
      <div class="bg-red-900/40 border border-red-700/50 rounded-xl p-3 mb-5">
        @foreach($errors->all() as $e)<div class="text-red-300 text-sm">{{ $e }}</div>@endforeach
      </div>
      @endif
      <form action="{{ route('organiser.password.email') }}" method="POST" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Email Address</label>
          <input type="email" name="email" value="{{ old('email') }}" required autofocus
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@company.com">
        </div>
        <button type="submit" class="w-full py-3.5 font-extrabold text-white rounded-xl text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          Send Reset Link →
        </button>
        <p class="text-center text-xs text-gray-500"><a href="{{ route('organiser.login') }}" class="text-indigo-400 hover:text-indigo-300">← Back to login</a></p>
      </form>
    </div>
  </div>
</div>
@endsection
