@extends('layouts.app')
@section('title', 'Server Error')
@section('content')
<main id="main-content" role="main" class="min-h-screen bg-gray-950 flex items-center justify-center px-4" aria-labelledby="error-heading">
  <div class="max-w-lg w-full text-center">
    <div class="w-24 h-24 bg-red-600/10 border border-red-500/20 rounded-full flex items-center justify-center mx-auto mb-8" aria-hidden="true">
      <span class="text-5xl font-black text-red-400">500</span>
    </div>
    <h1 id="error-heading" class="text-3xl font-extrabold text-white mb-3">Something Went Wrong</h1>
    <p class="text-gray-400 mb-8 leading-relaxed">We've encountered an unexpected error. Our team has been notified. Please try again in a few moments.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <button onclick="window.history.back()"
              class="inline-flex items-center justify-center gap-2 px-6 py-3 font-bold text-white rounded-xl text-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-950"
              style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
              aria-label="Go back to previous page">
        ← Go Back
      </button>
      <a href="{{ route('home') }}"
         class="inline-flex items-center justify-center gap-2 px-6 py-3 font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-xl text-sm transition-all focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-950"
         aria-label="Return to homepage">
        🏠 Home
      </a>
    </div>
    <p class="text-gray-600 text-xs mt-8">Error 500 · <a href="mailto:{{ config('ticketly.support_email') }}" class="text-indigo-400 hover:text-indigo-300 focus:outline-none focus:underline">Contact support</a> if this persists.</p>
  </div>
</main>
@endsection
