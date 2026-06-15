<script>
  (function () {
    var lockedTheme = document.documentElement.getAttribute('data-theme-lock');
    var savedTheme = localStorage.getItem('ticketly-theme');
    var defaultTheme = document.documentElement.getAttribute('data-theme-default');
    var resolvedDefault = (defaultTheme === 'light' || defaultTheme === 'dark') ? defaultTheme : 'dark';
    var resolvedSaved = (savedTheme === 'light' || savedTheme === 'dark') ? savedTheme : null;
    var theme = lockedTheme ? lockedTheme : (resolvedSaved ? resolvedSaved : resolvedDefault);
    document.documentElement.setAttribute('data-theme', theme);
  })();
</script>
<style>
  :root[data-theme='dark'] {
    --app-bg: #030712;
    --surface-1: #111827;
    --surface-2: #1f2937;
    --surface-3: #374151;
    --surface-border: #1f2937;
    --surface-border-2: #374151;
    --text-primary: #f9fafb;
    --text-2: #e5e7eb;
    --text-3: #d1d5db;
    --text-4: #9ca3af;
    --text-5: #6b7280;
    --text-6: #4b5563;
    --scroll-track: #0f172a;
    --scroll-thumb: #334155;
    --ring-offset: #111827;
    --kpi-bg: #111827;
    --kpi-border: #1f2937;
    --sidebar-hover-bg: rgba(255, 255, 255, 0.1);
    --brand-500: #6366f1;
    --brand-600: #4f46e5;
  }

  :root[data-theme='light'] {
    --app-bg: #f8f9fb;
    --surface-1: #ffffff;
    --surface-2: #f3f6fc;
    --surface-3: #e8eef8;
    --surface-border: #d9e1ee;
    --surface-border-2: #c7d2e4;
    --text-primary: #0f172a;
    --text-2: #1e293b;
    --text-3: #334155;
    --text-4: #475569;
    --text-5: #64748b;
    --text-6: #7f90a9;
    --scroll-track: #e4eaf4;
    --scroll-thumb: #9aacbf;
    --ring-offset: #f8f9fb;
    --kpi-bg: #ffffff;
    --kpi-border: #d8e0ed;
    --sidebar-hover-bg: rgba(79, 70, 229, 0.08);
    --brand-500: #6366f1;
    --brand-600: #4f46e5;
    --brand-700: #4338ca;
    --brand-200: #c7d2fe;
    --glass-bg: rgba(255, 255, 255, 0.74);
    --light-shadow-xs: 0 6px 16px rgba(15, 23, 42, 0.05);
    --light-shadow-sm: 0 10px 24px rgba(15, 23, 42, 0.07);
    --light-shadow-md: 0 16px 38px rgba(15, 23, 42, 0.09);
    --light-shadow-lg: 0 24px 56px rgba(15, 23, 42, 0.12);
  }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'Inter', sans-serif;
    background: var(--app-bg);
    color: var(--text-primary);
  }

  :focus-visible {
    outline: 2px solid var(--brand-500);
    outline-offset: 2px;
  }

  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: var(--scroll-track); }
  ::-webkit-scrollbar-thumb { background: var(--scroll-thumb); border-radius: 3px; }

  .bg-gray-950 { background-color: var(--app-bg) !important; }
  .bg-gray-900, .bg-gray-900\/50, .bg-gray-900\/80, .bg-gray-900\/95 { background-color: var(--surface-1) !important; }
  .bg-gray-800, .bg-gray-800\/50, .bg-gray-800\/60 { background-color: var(--surface-2) !important; }
  .bg-gray-700, .bg-gray-600 { background-color: var(--surface-3) !important; }

  .text-white { color: var(--text-primary) !important; }
  .text-gray-100 { color: var(--text-2) !important; }
  .text-gray-200 { color: var(--text-2) !important; }
  .text-gray-300 { color: var(--text-3) !important; }
  .text-gray-400 { color: var(--text-4) !important; }
  .text-gray-500 { color: var(--text-5) !important; }
  .text-gray-600 { color: var(--text-6) !important; }

  .border-gray-900, .border-gray-800 { border-color: var(--surface-border) !important; }
  .border-gray-700, .border-gray-600 { border-color: var(--surface-border-2) !important; }
  .focus\:ring-offset-gray-900:focus, .focus\:ring-offset-gray-950:focus { --tw-ring-offset-color: var(--ring-offset) !important; }
  .placeholder-gray-500::placeholder, .placeholder-gray-400::placeholder { color: var(--text-5) !important; }

  .hover\:bg-gray-800:hover { background-color: var(--surface-2) !important; }
  .hover\:bg-gray-700:hover, .hover\:bg-gray-600:hover { background-color: var(--surface-3) !important; }

  .kpi-card { background: var(--kpi-bg); border-color: var(--kpi-border); }
  .sidebar-link { color: var(--text-4); }
  .sidebar-link:hover { color: var(--text-primary); background: var(--sidebar-hover-bg); }

  .theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 0.75rem;
    border: 1px solid var(--surface-border-2);
    background: var(--surface-2);
    color: var(--text-3);
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.2s ease;
  }
  .theme-toggle:hover {
    color: var(--text-primary);
    background: var(--surface-3);
    transform: translateY(-1px);
  }

  .password-toggle-field {
    position: relative;
    display: block;
    width: 100%;
  }

  .password-toggle-field > input {
    padding-right: 3rem !important;
  }

  .password-toggle-button {
    position: absolute;
    top: 50%;
    right: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    padding: 0;
    border: 0;
    border-radius: 9999px;
    background: transparent !important;
    color: var(--text-4);
    cursor: pointer;
    transform: translateY(-50%);
    transition: color 0.2s ease;
  }

  .password-toggle-button:hover {
    background: transparent !important;
    color: var(--text-primary) !important;
    transform: translateY(-50%);
    box-shadow: none !important;
  }

  .password-toggle-button:focus-visible {
    outline: 2px solid var(--brand-500);
    outline-offset: 2px;
  }

  .password-toggle-button svg {
    width: 1.1rem;
    height: 1.1rem;
    pointer-events: none;
  }

  :root[data-theme='light'] body {
    background-image:
      /* radial-gradient(circle at 10% -10%, rgba(99, 102, 241, 0.11), transparent 40%),
      radial-gradient(circle at 85% 5%, rgba(59, 130, 246, 0.1), transparent 36%),
      radial-gradient(circle at 50% 120%, rgba(14, 165, 233, 0.08), transparent 40%), */
      linear-gradient(180deg, #ffffff 0%, #ffffff 100%);
  }

  :root[data-theme='light'] a,
  :root[data-theme='light'] button,
  :root[data-theme='light'] input,
  :root[data-theme='light'] select,
  :root[data-theme='light'] textarea {
    transition: all 0.24s ease;
  }

  :root[data-theme='light'] h1,
  :root[data-theme='light'] h2,
  :root[data-theme='light'] h3 {
    letter-spacing: -0.015em;
  }

  :root[data-theme='light'] h1 { line-height: 1.15; }
  :root[data-theme='light'] h2 { line-height: 1.2; }
  :root[data-theme='light'] p { line-height: 1.6; }

  :root[data-theme='light'] main h1.text-3xl,
  :root[data-theme='light'] main h1.text-4xl {
    font-size: clamp(1.9rem, 3.2vw, 2.85rem) !important;
  }

  :root[data-theme='light'] main h2.text-2xl {
    font-size: clamp(1.4rem, 2.1vw, 2.05rem) !important;
  }

  :root[data-theme='light'] nav[aria-label='Main navigation'] {
    background: var(--glass-bg) !important;
    border-color: var(--surface-border) !important;
    box-shadow: var(--light-shadow-md);
    backdrop-filter: blur(14px) saturate(140%);
    -webkit-backdrop-filter: blur(14px) saturate(140%);
  }

  :root[data-theme='light'] footer[role='contentinfo'] {
    background: rgba(248, 250, 255, 0.9) !important;
    border-color: var(--surface-border) !important;
  }

  :root[data-theme='light'] section[aria-label='Hero'] {
    background: linear-gradient(135deg, #eef3ff 0%, #e8f0ff 44%, #f8f9fb 100%) !important;
    border-bottom: 1px solid var(--surface-border);
  }

  :root[data-theme='light'] section[aria-label='Hero'] .bg-white\/10 {
    background: rgba(255, 255, 255, 0.82) !important;
    border-color: rgba(79, 70, 229, 0.18) !important;
    color: #3f3cbb !important;
    box-shadow: var(--light-shadow-xs);
  }

  :root[data-theme='light'] section[aria-label='Hero'] .border-white\/20,
  :root[data-theme='light'] section[aria-label='Hero'] .border-white\/10 {
    border-color: rgba(148, 163, 184, 0.36) !important;
  }

  :root[data-theme='light'] section[aria-label='Hero'] .bg-white\/10.backdrop-blur-md {
    backdrop-filter: blur(8px);
  }

  :root[data-theme='light'] section[aria-labelledby='how-it-works-heading'] {
    background: transparent !important;
    border-color: var(--surface-border) !important;
  }

  :root[data-theme='light'] .bg-gray-900.border,
  :root[data-theme='light'] .bg-gray-900\/50.border,
  :root[data-theme='light'] .bg-gray-800\/50.border,
  :root[data-theme='light'] .kpi-card {
    background: var(--surface-1) !important;
    border-color: var(--surface-border) !important;
    box-shadow: var(--light-shadow-sm);
  }

  :root[data-theme='light'] .rounded-2xl {
    border-radius: 1.1rem;
  }

  :root[data-theme='light'] article.group.bg-gray-900 {
    border-color: var(--surface-border) !important;
    border-radius: 1.35rem;
    box-shadow: var(--light-shadow-sm);
    transition: transform 0.26s ease, box-shadow 0.26s ease, border-color 0.26s ease;
  }

  :root[data-theme='light'] article.group.bg-gray-900:hover {
    border-color: rgba(79, 70, 229, 0.42) !important;
    transform: translateY(-6px);
    box-shadow: var(--light-shadow-lg);
  }

  :root[data-theme='light'] article.group.bg-gray-900 h3 {
    font-size: 1.02rem;
    line-height: 1.45;
  }

  :root[data-theme='light'] article.group.bg-gray-900 .p-4 {
    padding: 1.1rem 1.1rem 1.2rem;
  }

  :root[data-theme='light'] .kpi-card {
    border-radius: 1.2rem;
    padding: 1.35rem;
    transition: transform 0.24s ease, border-color 0.24s ease, box-shadow 0.24s ease;
  }

  :root[data-theme='light'] .kpi-card:hover {
    transform: translateY(-4px);
    border-color: var(--brand-200);
    box-shadow: var(--light-shadow-md);
  }

  :root[data-theme='light'] .bg-indigo-600,
  :root[data-theme='light'] button[style*='linear-gradient(135deg,#6366f1,#8b5cf6)'],
  :root[data-theme='light'] a[style*='linear-gradient(135deg,#6366f1,#8b5cf6)'] {
    background: linear-gradient(135deg, var(--brand-600), var(--brand-500)) !important;
    color: #ffffff !important;
    border: 1px solid rgba(67, 56, 202, 0.08);
    box-shadow: 0 12px 24px rgba(79, 70, 229, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.22);
  }

  :root[data-theme='light'] .hover\:bg-indigo-700:hover,
  :root[data-theme='light'] .bg-indigo-600:hover,
  :root[data-theme='light'] button[style*='linear-gradient(135deg,#6366f1,#8b5cf6)']:hover,
  :root[data-theme='light'] a[style*='linear-gradient(135deg,#6366f1,#8b5cf6)']:hover {
    background: linear-gradient(135deg, var(--brand-700), var(--brand-600)) !important;
    transform: translateY(-1px);
    box-shadow: 0 16px 28px rgba(67, 56, 202, 0.27), inset 0 1px 0 rgba(255, 255, 255, 0.22);
  }

  :root[data-theme='light'] .bg-indigo-600:active,
  :root[data-theme='light'] button[style*='linear-gradient(135deg,#6366f1,#8b5cf6)']:active,
  :root[data-theme='light'] a[style*='linear-gradient(135deg,#6366f1,#8b5cf6)']:active {
    transform: translateY(0);
    box-shadow: 0 8px 18px rgba(67, 56, 202, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.18);
  }

  :root[data-theme='light'] .bg-indigo-600\/20 {
    background-color: rgba(79, 70, 229, 0.1) !important;
  }

  :root[data-theme='light'] [style*='linear-gradient'] .text-white,
  :root[data-theme='light'] [style*='linear-gradient'].text-white,
  :root[data-theme='light'] .bg-black\/60 .text-white,
  :root[data-theme='light'] .bg-black\/70 .text-white,
  :root[data-theme='light'] .bg-black\/60.text-white,
  :root[data-theme='light'] .bg-black\/70.text-white,
  :root[data-theme='light'] .bg-red-600.text-white,
  :root[data-theme='light'] .bg-emerald-600.text-white {
    color: #ffffff !important;
  }

  :root[data-theme='light'] #sidebar,
  :root[data-theme='light'] .flex-1 > header.bg-gray-900\/80 {
    background: var(--glass-bg) !important;
    border-color: var(--surface-border) !important;
    box-shadow: var(--light-shadow-sm);
    backdrop-filter: blur(12px) saturate(140%);
    -webkit-backdrop-filter: blur(12px) saturate(140%);
  }

  :root[data-theme='light'] .sidebar-link.active {
    background: rgba(79, 70, 229, 0.14);
    color: #3730a3;
    border-color: rgba(79, 70, 229, 0.3);
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);
  }

  :root[data-theme='light'] input.bg-gray-800,
  :root[data-theme='light'] input.bg-gray-900,
  :root[data-theme='light'] select.bg-gray-800,
  :root[data-theme='light'] select.bg-gray-900,
  :root[data-theme='light'] textarea.bg-gray-800 {
    background-color: #fbfcff !important;
    border-color: #d3ddeb !important;
    color: var(--text-primary) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
  }

  :root[data-theme='light'] input.bg-gray-800:focus,
  :root[data-theme='light'] input.bg-gray-900:focus,
  :root[data-theme='light'] select.bg-gray-800:focus,
  :root[data-theme='light'] select.bg-gray-900:focus,
  :root[data-theme='light'] textarea.bg-gray-800:focus {
    border-color: #7f90ff !important;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.95);
  }

  :root[data-theme='light'] input[type='checkbox'],
  :root[data-theme='light'] input[type='radio'] {
    accent-color: var(--brand-600);
  }

  :root[data-theme='light'] #payment-message {
    background-color: rgba(239, 68, 68, 0.08) !important;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  :root[data-theme='light'] .bg-amber-900\/30 {
    background-color: rgba(245, 158, 11, 0.12) !important;
    border-color: rgba(217, 119, 6, 0.3) !important;
  }

  :root[data-theme='light'] .text-amber-300 { color: #92400e !important; }
  :root[data-theme='light'] .text-amber-400 { color: #b45309 !important; }
  :root[data-theme='light'] .text-amber-400\/60 { color: rgba(120, 53, 15, 0.75) !important; }

  :root[data-theme='light'] .bg-red-900\/40,
  :root[data-theme='light'] .bg-red-900\/50,
  :root[data-theme='light'] .bg-red-900\/30,
  :root[data-theme='light'] .bg-red-900\/20 {
    background-color: rgba(239, 68, 68, 0.09) !important;
  }

  :root[data-theme='light'] .bg-emerald-900\/40,
  :root[data-theme='light'] .bg-emerald-900\/50,
  :root[data-theme='light'] .bg-blue-900\/40,
  :root[data-theme='light'] .bg-blue-900\/50,
  :root[data-theme='light'] .bg-yellow-900\/40 {
    box-shadow: var(--light-shadow-sm);
  }

  :root[data-theme='light'] .text-red-300 { color: #b91c1c !important; }
  :root[data-theme='light'] .text-emerald-300 { color: #047857 !important; }
  :root[data-theme='light'] .text-blue-300 { color: #1d4ed8 !important; }

  :root[data-theme='light'] .flex-1 > main {
    padding-top: 1.6rem;
  }

  @media (max-width: 768px) {
  :root[data-theme='light'] .theme-toggle { padding: 0.45rem 0.65rem; font-size: 0.7rem; }
  :root[data-theme='light'] article.group.bg-gray-900:hover { transform: translateY(-3px); }
  }

  @media (max-width: 640px) {
    :root[data-theme='light'] main h1.text-3xl,
    :root[data-theme='light'] main h1.text-4xl {
      font-size: 1.85rem !important;
    }
  }

  :root[data-theme='light'] [class*='hover:bg-']:hover:not(.password-toggle-button),
  :root[data-theme='light'] .theme-toggle:hover,
  :root[data-theme='light'] .admin-link:hover,
  :root[data-theme='light'] .sidebar-link:hover,
  :root[data-theme='light'] article.group:hover,
  :root[data-theme='light'] a.group:hover,
  :root[data-theme='light'] .kpi-card:hover,
  :root[data-theme='light'] [role='menuitem']:hover {
    background-color: #f3f3f3 !important;
    background-image: none !important;
  }
</style>
