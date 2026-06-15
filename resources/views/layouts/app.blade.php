@php($allowPublicThemeToggle = request()->routeIs('organiser.*', 'login', 'register', 'password.*') && !request()->routeIs('organiser.login', 'organiser.register'))
<!DOCTYPE html>
<html lang="en" @unless($allowPublicThemeToggle) data-theme-lock="light" @endunless>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- WCAG 2.1 AA: colour contrast, skip link, landmark roles --}}
<title>@yield('title', 'Ticketly') – Ticketly</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='8' y1='8' x2='56' y2='56' gradientUnits='userSpaceOnUse'%3E%3Cstop stop-color='%236366f1'/%3E%3Cstop offset='1' stop-color='%23a855f7'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect x='8' y='10' width='48' height='44' rx='12' fill='url(%23g)'/%3E%3Cpath d='M22 24h20a4 4 0 0 0 4 4v8a4 4 0 0 0-4 4H22a4 4 0 0 0-4-4v-8a4 4 0 0 0 4-4Z' fill='white' fill-opacity='.96'/%3E%3Cpath d='M32 24v16' stroke='url(%23g)' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('partials.theme-system-head')
@include('partials.global-ticket-loader-head')
@yield('head')
</head>
<body class="bg-gray-950 text-gray-100">
@include('partials.global-ticket-loader')

{{-- Public Navigation --}}
<header role="banner" class="sticky top-0 z-50">
  <nav class="w-full border-b" style="position: sticky; top: 0; z-index: 50; background: rgba(59, 63, 73, 0.95) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-color: rgba(58,62,72,0.90) !important; color: #d2d6de !important;" aria-label="Main navigation">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3 py-3 md:h-[4.5rem] md:justify-start md:py-0">
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="Ticketly – return to home">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)" aria-hidden="true">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
          </div>
          <span class="text-lg font-extrabold tracking-tight sm:text-xl" style="color:#f5f7fb;">Ticketly</span>
        </a>

        <button id="mobile-menu-toggle" class="ml-auto md:ml-0 md:hidden inline-flex items-center justify-center rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false" style="color:#f5f7fb;">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <div class="hidden items-center gap-6 md:ml-6 md:flex">
          <a href="{{ route('home') }}" class="text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Explore</a>
          <a href="{{ route('events.index') }}" class="text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Categories</a>
          <a href="{{ route('organiser.register') }}" class="text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Become an Organiser</a>
        </div>

        <div class="hidden md:ml-auto md:flex items-center gap-2 sm:gap-4">
          <a href="{{ route('organiser.login') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-indigo-700  sm:px-5 sm:text-sm">Login</a>
        </div>
      </div>

      <div id="mobile-menu" class="hidden md:hidden overflow-hidden border-t pb-4 pt-3" style="border-color:rgba(255,255,255,0.08);" aria-hidden="true">
        <div class="flex flex-col items-stretch gap-2 text-center">
          <a href="{{ route('home') }}" data-mobile-menu-link class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Explore</a>
          <a href="{{ route('events.index') }}" data-mobile-menu-link class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Categories</a>
          <a href="{{ route('organiser.register') }}" data-mobile-menu-link class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-medium transition-colors" style="color:#c5c9d2;" onmouseover="this.style.color='#f5f7fb'" onmouseout="this.style.color='#c5c9d2'">Become an Organiser</a>
          <a href="{{ route('organiser.login') }}" data-mobile-menu-link class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-xs font-semibold text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900 sm:px-5 sm:text-sm">Login</a>
        </div>
      </div>
    </div>  </nav>
</header>

<main id="main-content" role="main" tabindex="-1">
  @yield('content')
</main>

{{-- Public Footer --}}
<footer
  role="contentinfo"
  class="mt-8 border-t"
  style="--footer-bg1:#0b0d12; --footer-bg2:#07080c; --footer-border:#1f2430; --footer-heading:#f1f5f9; --footer-text:#cbd5e1; --footer-muted:#94a3b8; --footer-icon-bg:#141821; --footer-input-bg:#0f131b; --footer-input-border:#1f2430; background:linear-gradient(180deg,var(--footer-bg1),var(--footer-bg2)) !important; border-color:var(--footer-border) !important; color:var(--footer-text) !important;"
>
  <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-8">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr_1fr_1fr]">
      <div>
        <div class="mb-7 flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#6d28d9,#8b5cf6)" aria-hidden="true">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
          </div>
          <span class="text-[1.8rem] font-bold tracking-[-0.03em] sm:text-[2rem]" style="color:var(--footer-heading);">ticketly</span>
        </div>
        <p class="max-w-[360px] text-[0.98rem] leading-8 sm:text-[1.02rem] sm:leading-9" style="color:var(--footer-muted);">
          Discover and book tickets to the best events near you. From concerts to conferences, we've got you covered.
        </p>
        <div class="mt-7 flex items-center gap-4">
          <a href="#" class="flex h-11 w-11 items-center justify-center rounded-xl transition sm:h-12 sm:w-12" style="background:var(--footer-icon-bg); color:var(--footer-text);" aria-label="Twitter">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.5 6.2c-.6.3-1.3.5-2 .6.7-.5 1.2-1.1 1.5-1.9-.7.4-1.5.8-2.3.9A3.5 3.5 0 0 0 12.8 9c0 .3 0 .6.1.8A10 10 0 0 1 5.1 5.8a3.5 3.5 0 0 0 1.1 4.7c-.6 0-1.1-.2-1.6-.4 0 1.7 1.2 3.1 2.8 3.4-.3.1-.6.1-1 .1-.2 0-.5 0-.7-.1.5 1.5 1.9 2.6 3.6 2.6A7 7 0 0 1 4 17.6a9.9 9.9 0 0 0 5.4 1.6c6.5 0 10-5.4 10-10v-.5c.7-.5 1.3-1.1 1.8-1.8Z"/></svg>
          </a>
          <a href="#" class="flex h-11 w-11 items-center justify-center rounded-xl transition sm:h-12 sm:w-12" style="background:var(--footer-icon-bg); color:var(--footer-text);" aria-label="Instagram">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" stroke-width="2"/><circle cx="12" cy="12" r="3.75" stroke-width="2"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor" stroke="none"/></svg>
          </a>
          <a href="#" class="flex h-11 w-11 items-center justify-center rounded-xl transition sm:h-12 sm:w-12" style="background:var(--footer-icon-bg); color:var(--footer-text);" aria-label="Facebook">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.4 21v-7.2h2.4l.4-2.8h-2.8V9.2c0-.8.2-1.4 1.4-1.4h1.5V5.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 4v1.9H7.8v2.8h2.4V21h3.2Z"/></svg>
          </a>
        </div>
      </div>

      <div>
        <h3 class="text-[1rem] font-bold" style="color:var(--footer-heading);">Company</h3>
        <ul class="mt-2 space-y-3 text-[1rem]" style="color:var(--footer-muted);">
          <li><a href="{{ route('home') }}" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">About</a></li>
          <li><a href="{{ route('events.index') }}" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">FAQ</a></li>
          <li><a href="mailto:{{ config('ticketly.support_email') }}" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">Contact</a></li>
        </ul>
      </div>

      <div class="space-y-12">
        <div>
          <h3 class="text-[1rem] font-bold" style="color:var(--footer-heading);">Legal</h3>
          <ul class="mt-4 space-y-3 text-[1rem]" style="color:var(--footer-muted);">
            <li><a href="#" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">Terms of Service</a></li>
            <li><a href="#" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">Privacy Policy</a></li>
          </ul>
        </div>
       
      </div>

      <div>
          <h3 class="text-[1rem] font-bold" style="color:var(--footer-heading);">For Organisers</h3>
          <ul class="mt-4 space-y-3 text-[1rem]" style="color:var(--footer-muted);">
            <li><a href="{{ route('organiser.register') }}" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">Become an Organiser</a></li>
            <li><a href="{{ route('organiser.login') }}" class="transition" style="color:var(--footer-muted);" onmouseover="this.style.color='var(--footer-heading)'" onmouseout="this.style.color='var(--footer-muted)'">Organiser Login</a></li>
          </ul>
        </div>
    </div>

    <div class="mt-16 border-t pt-4 text-center text-[1rem]" style="border-color:var(--footer-border); color:var(--footer-muted);">
      <p>&copy; {{ date('Y') }} <span class="font-semibold" style="color:var(--footer-text);">ticketly</span>. All rights reserved.</p>
    </div>
  </div>
</footer>

@if($allowPublicThemeToggle)
@include('partials.theme-system-script')
@endif
@include('partials.password-toggle-script')
<script>
  (function () {
    var toggle = document.getElementById('mobile-menu-toggle');
    var menu = document.getElementById('mobile-menu');
    var menuLinks = document.querySelectorAll('[data-mobile-menu-link]');
    if (!toggle || !menu) return;

    function openMenu() {
      menu.classList.remove('hidden');
      menu.setAttribute('aria-hidden', 'false');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.setAttribute('aria-label', 'Close menu');
      document.body.classList.add('overflow-hidden');
    }

    function closeMenu() {
      menu.classList.add('hidden');
      menu.setAttribute('aria-hidden', 'true');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open menu');
      document.body.classList.remove('overflow-hidden');
    }

    toggle.addEventListener('click', function () {
      var isHidden = menu.classList.contains('hidden');
      if (isHidden) {
        openMenu();
      } else {
        closeMenu();
      }
    });

    menuLinks.forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
        closeMenu();
      }
    });

    document.addEventListener('click', function (event) {
      if (menu.classList.contains('hidden')) return;
      if (menu.contains(event.target) || toggle.contains(event.target)) return;
      closeMenu();
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 768) {
        closeMenu();
      }
    });
  })();
</script>
@if(request()->routeIs('organiser.login', 'admin.login'))
@include('partials.login-history-guard-script', ['guardNamespace' => request()->routeIs('admin.login') ? 'admin' : 'organiser'])
@endif
@yield('scripts')
@include('partials.date-input-display')
@include('partials.global-ticket-loader-script')
</body>
</html>


