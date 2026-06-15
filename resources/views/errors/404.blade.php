@extends('layouts.app')
@section('title', 'Page Not Found')
@section('content')
<main id="main-content" role="main" class="min-h-screen bg-gray-950 flex items-center justify-center px-4" aria-labelledby="error-heading">
  <div class="max-w-lg w-full text-center">
    <div class="w-24 h-24 bg-indigo-600/10 border border-indigo-500/20 rounded-full flex items-center justify-center mx-auto mb-8" aria-hidden="true">
      <span class="text-5xl font-black text-indigo-400">404</span>
    </div>
    <h1 id="error-heading" class="text-3xl font-extrabold text-white mb-3">Page Not Found</h1>
    <p class="text-gray-400 mb-8 leading-relaxed">The page you're looking for doesn't exist or may have been moved. Please check the URL or return to the homepage.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('home') }}"
         class="inline-flex items-center justify-center gap-2 px-6 py-3 font-bold text-white rounded-xl text-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-950"
         style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
         aria-label="Return to homepage">
        🏠 Back to Home
      </a>
      <a href="{{ route('events.index') }}"
         class="inline-flex items-center justify-center gap-2 px-6 py-3 font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-xl text-sm transition-all focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-950"
         aria-label="Browse all events">
        🎟 Browse Events
      </a>
    </div>
    <p class="text-gray-600 text-xs mt-8">Error 404 · If you believe this is a mistake, <a href="mailto:{{ config('ticketly.support_email') }}" class="text-indigo-400 hover:text-indigo-300 focus:outline-none focus:underline">contact support</a>.</p>
  </div>
</main>
@endsection
