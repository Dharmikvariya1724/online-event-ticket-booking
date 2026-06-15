@extends('layouts.app')
@section('title', 'Session Expired')
@section('content')
<main id="main-content" role="main" class="min-h-screen bg-gray-950 flex items-center justify-center px-4" aria-labelledby="error-heading">
  <div class="max-w-md w-full text-center">
    <div class="w-20 h-20 bg-amber-600/10 border border-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-8" aria-hidden="true">
      <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <h1 id="error-heading" class="text-2xl font-extrabold text-white mb-3">Session Expired</h1>
    <p class="text-gray-400 mb-8">Your session has expired for security reasons. Please go back and try your action again.</p>
    <button onclick="window.history.back()"
            class="inline-flex items-center justify-center gap-2 px-6 py-3 font-bold text-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-950"
            style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
            aria-label="Go back to previous page">
      ← Go Back
    </button>
  </div>
</main>
@endsection
