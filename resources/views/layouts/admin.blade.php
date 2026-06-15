<!DOCTYPE html>
<html lang="en" data-theme-default="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') - Ticketly</title>
@include('partials.protected-history-guard-head', ['guardNamespace' => 'admin', 'guardState' => session('admin_auth_state'), 'fallbackRedirect' => route('admin.login')])
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@include('partials.theme-system-head')
@include('partials.global-ticket-loader-head')
<style>
  @media (max-width: 767px) {
    #admin-sidebar {
      position: fixed;
      inset: 0 auto 0 0;
      z-index: 50;
      width: min(18rem, 82vw);
      transform: translateX(-100%);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }
    #admin-sidebar.sidebar-open { transform: translateX(0); }
    #admin-backdrop {
      position: fixed;
      inset: 0;
      z-index: 40;
      background: rgba(15, 23, 42, 0.42);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
    #admin-backdrop.sidebar-open { opacity: 1; pointer-events: auto; }
  }
  .admin-link {
    display:flex;
    align-items:center;
    gap:0.7rem;
    padding:0.7rem 0.9rem;
    border-radius:0.75rem;
    font-size:0.88rem;
    font-weight:600;
    transition:all .2s ease;
  }
  .admin-link.active {
    background: rgba(20, 184, 166, 0.15);
    color: #5eead4;
    border: 1px solid rgba(20, 184, 166, 0.35);
  }
  .badge {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:1.6rem;
    padding:0.3rem 0.7rem;
    border-radius:9999px;
    font-size:0.72rem;
    font-weight:700;
    border:1px solid #d1d5db;
    background:#f3f4f6;
    color:#374151;
  }
  .badge--positive { color:#166534;background:#dcfce7;border-color:#86efac; }
  .badge--warning { color:#92400e;background:#fffbeb;border-color:#fde68a; }
  .badge--danger { color:#991b1b;background:#fef2f2;border-color:#fecaca; }
  .badge--neutral { color:#374151;background:#f3f4f6;border-color:#d1d5db; }
</style>
@yield('head')
</head>
<body class="bg-gray-950 text-gray-100 @yield('body-class')">
@include('partials.global-ticket-loader')

<div class="flex h-screen overflow-hidden">
  <div id="admin-backdrop" onclick="closeAdminSidebar()" aria-hidden="true"></div>

  <aside id="admin-sidebar" class="w-64 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col h-full overflow-y-auto transition-all duration-300" style="min-width:256px">
    <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-800">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#0ea5e9,#14b8a6,#10b981)">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v6c0 5-3 9-7 11-4-2-7-6-7-11V7l7-4z"/></svg>
      </div>
      <div>
        <div class="text-sm font-extrabold text-white">Ticketly</div>
        <div class="text-xs text-emerald-400 font-medium">Admin Panel</div>
      </div>
      <button type="button" onclick="closeAdminSidebar()" class="md:hidden ml-auto inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white" aria-label="Close sidebar">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1">
      <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mb-2">Overview</div>
      <a href="{{ route('admin.dashboard') }}" class="admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>
      <a href="{{ route('admin.reports.index') }}" class="admin-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M4 19h16"/></svg>
        Reports
      </a>

      <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mt-4 mb-2">Management</div>
      <!-- <a href="{{ route('admin.customers.index') }}" class="admin-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Customers
      </a> -->
      <a href="{{ route('admin.organisers.index') }}" class="admin-link {{ request()->routeIs('admin.organisers.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 6H7a2 2 0 01-2-2V8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2z"/></svg>
        Organisers
      </a>
      <a href="{{ route('admin.events.index') }}" class="admin-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Events
      </a>
      <a href="{{ route('admin.orders.index') }}" class="admin-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        Orders
      </a>
      <!-- <a href="{{ route('admin.payouts.index') }}" class="admin-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5S13.657 14 12 14m0-6V7m0 7v1m-7-3a9 9 0 1018 0 9 9 0 10-18 0z"/></svg>
        Payouts
      </a> -->

      <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mt-4 mb-2">System</div>
      <a href="{{ route('admin.settings.index') }}" class="admin-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l.7 2.154a1 1 0 00.95.69h2.265c.969 0 1.371 1.24.588 1.81l-1.833 1.332a1 1 0 00-.364 1.118l.7 2.154c.3.921-.755 1.688-1.54 1.118l-1.833-1.332a1 1 0 00-1.176 0l-1.833 1.332c-.784.57-1.838-.197-1.539-1.118l.7-2.154a1 1 0 00-.364-1.118L2.61 7.581c-.783-.57-.38-1.81.588-1.81h2.265a1 1 0 00.95-.69l.7-2.154z"/></svg>
        Settings
      </a>
      <!-- <a href="{{ route('admin.emails.index') }}" class="admin-link {{ request()->routeIs('admin.emails.*') ? 'active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 9h18V7H3v10z"/></svg>
        Email Logs
      </a> -->
      <a href="{{ route('home') }}" target="_blank" class="admin-link">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        View Site
      </a>
    </nav>

    <div class="px-4 py-4 border-t border-gray-800">
      @php $admin = request()->attributes->get('admin'); @endphp
      @if($admin)
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0" style="background:linear-gradient(135deg,#0ea5e9,#14b8a6)">
          {{ strtoupper(substr($admin->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-semibold text-white truncate">{{ $admin->name }}</div>
          <div class="text-xs text-gray-400 truncate">{{ $admin->email }}</div>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST" data-logout-guard data-logout-namespace="admin" data-logout-state="{{ session('admin_auth_state') }}" data-logout-redirect="{{ route('admin.login') }}">
          @csrf
          <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-gray-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          </button>
        </form>
      </div>
      @endif
    </div>
  </aside>

  <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-gray-900/80 backdrop-blur border-b border-gray-800 px-7 py-4 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-3">
        <button onclick="toggleAdminSidebar()" class="md:hidden p-2 rounded-lg hover:bg-gray-800 text-gray-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-base font-bold text-white">@yield('page-title', 'Admin')</h1>
          <p class="text-xs text-gray-500">@yield('page-subtitle', ticketly_format_date(now()))</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle color theme">
          <span data-theme-label>Dark</span>
        </button>
      </div>
    </header>

    @if(session('success'))
    <div class="mx-6 mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('info'))
    <div class="mx-6 mt-4 flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
      <span class="font-medium">{{ session('info') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mx-6 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      @foreach($errors->all() as $error)
      <div class="flex items-start gap-2">
        <span class="pt-0.5 text-rose-500">&bull;</span>
        <span>{{ $error }}</span>
      </div>
      @endforeach
    </div>
    @endif

    <main class="flex-1 overflow-y-auto p-6 lg:p-7 xl:p-8">
      @yield('content')
    </main>
  </div>
</div>

<div id="admin-confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/60" data-confirm-backdrop></div>
  <div class="relative w-full max-w-md rounded-2xl border border-gray-800 bg-gray-900 p-6 shadow-2xl">
    <div class="flex items-start gap-3">
      <div class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 005.1 19h13.8a2 2 0 001.73-2.84l-7.1-12.3a2 2 0 00-3.46 0z"/></svg>
      </div>
      <div class="flex-1">
        <h3 id="admin-confirm-title" class="text-base font-semibold text-white">Confirm action</h3>
        <p id="admin-confirm-message" class="mt-2 text-sm text-gray-400">Are you sure you want to continue?</p>
      </div>
    </div>
    <div class="mt-6 flex items-center justify-end gap-3">
      <button type="button" id="admin-confirm-cancel" class="px-4 py-2 rounded-xl border border-gray-700 text-gray-300 text-sm">Cancel</button>
      <button type="button" id="admin-confirm-ok" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Confirm</button>
    </div>
  </div>
</div>

@include('partials.theme-system-script')
@include('partials.password-toggle-script')
@include('partials.protected-history-guard-script', ['guardNamespace' => 'admin', 'guardState' => session('admin_auth_state'), 'fallbackRedirect' => route('admin.login')])
<script>
  function toggleAdminSidebar() {
    var sidebar = document.getElementById('admin-sidebar');
    var backdrop = document.getElementById('admin-backdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.toggle('sidebar-open');
    backdrop.classList.toggle('sidebar-open');
  }
  function closeAdminSidebar() {
    var sidebar = document.getElementById('admin-sidebar');
    var backdrop = document.getElementById('admin-backdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove('sidebar-open');
    backdrop.classList.remove('sidebar-open');
  }

  (function () {
    var modal = document.getElementById('admin-confirm-modal');
    var titleEl = document.getElementById('admin-confirm-title');
    var messageEl = document.getElementById('admin-confirm-message');
    var okBtn = document.getElementById('admin-confirm-ok');
    var cancelBtn = document.getElementById('admin-confirm-cancel');
    var backdrop = modal ? modal.querySelector('[data-confirm-backdrop]') : null;
    var pendingForm = null;

    if (!modal || !okBtn || !cancelBtn || !backdrop) return;

    function openModal(form) {
      pendingForm = form;
      titleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm action';
      messageEl.textContent = form.getAttribute('data-confirm') || 'Are you sure you want to continue?';
      okBtn.textContent = form.getAttribute('data-confirm-ok') || 'Confirm';
      cancelBtn.textContent = form.getAttribute('data-confirm-cancel') || 'Cancel';
      modal.classList.remove('hidden');
    }

    function closeModal() {
      modal.classList.add('hidden');
      pendingForm = null;
    }

    okBtn.addEventListener('click', function () {
      if (!pendingForm) return closeModal();
      var form = pendingForm;
      closeModal();
      form.submit();
    });

    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeModal();
    });

    document.addEventListener('submit', function (event) {
      var form = event.target.closest('form[data-confirm]');
      if (!form) return;
      event.preventDefault();
      openModal(form);
    });
  })();
</script>
@yield('scripts')
@include('partials.date-input-display')
@include('partials.global-ticket-loader-script')
</body>
</html>
