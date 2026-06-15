@extends('layouts.app')
@section('title', 'Account Pending Approval')
@section('content')
<div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
  <div class="max-w-md w-full text-center">
    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background:rgba(99,102,241,0.1);border:2px solid rgba(99,102,241,0.3)">
      <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <h1 class="text-2xl font-extrabold text-white mb-3">Account Pending Approval</h1>
    <p class="text-gray-400 leading-relaxed mb-6">Thank you for registering! Your organiser account is being reviewed by our team. Approvals are typically completed within <strong class="text-white">24 hours</strong>.</p>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 text-left space-y-3 mb-8">
      @foreach(['Account created successfully','Approval review in progress','You\'ll be notified by email once approved','Log in after approval to access your dashboard'] as $i => $step)
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold {{ $i === 0 ? 'bg-emerald-600 text-white' : ($i === 1 ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-400') }}">{{ $i + 1 }}</div>
        <span class="text-sm {{ $i <= 1 ? 'text-white' : 'text-gray-400' }}">{{ $step }}</span>
      </div>
      @endforeach
    </div>
    <a href="{{ route('organiser.login') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Already approved? Sign in →</a>
  </div>
</div>
@endsection
