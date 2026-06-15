@extends('layouts.app')
@section('title', 'Ticketly - Find Events You Love. Sell Tickets for Free.')

@section('head')
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #fff; color: #111; }
  a { text-decoration: none; color: inherit; }
  img { display: block; max-width: 100%; }

  /* -- Utilities -- */
  .container { max-width: 1280px; margin: 0 auto; padding: 0 2rem; }
  .container-md { max-width: 1024px; margin: 0 auto; padding: 0 2rem; }
  .text-center { text-align: center; }
  .flex { display: flex; }
  .flex-col { flex-direction: column; }
  .items-center { align-items: center; }
  .justify-between { justify-content: space-between; }
  .gap-2 { gap: .5rem; }
  .gap-3 { gap: .75rem; }
  .gap-4 { gap: 1rem; }
  .gap-6 { gap: 1.5rem; }
  .gap-8 { gap: 2rem; }
  .gap-16 { gap: 4rem; }
  .grid { display: grid; }
  .grid-2 { grid-template-columns: repeat(2, 1fr); }
  .grid-3 { grid-template-columns: repeat(3, 1fr); }
  .grid-4 { grid-template-columns: repeat(4, 1fr); }
  .grid-6 { grid-template-columns: repeat(6, 1fr); }
  .grid-8 { grid-template-columns: repeat(8, 1fr); }
  .relative { position: relative; }
  .absolute { position: absolute; }
  .overflow-hidden { overflow: hidden; }
  .rounded-full { border-radius: 9999px; }
  .rounded-xl { border-radius: .75rem; }
  .rounded-2xl { border-radius: 1rem; }
  .rounded-3xl { border-radius: 1.5rem; }
  .shadow-xl { box-shadow: 0 20px 40px rgba(0,0,0,.12); }
  .shadow-2xl { box-shadow: 0 25px 60px rgba(0,0,0,.18); }
  .w-full { width: 100%; }
  .h-full { height: 100%; }
  .object-cover { object-fit: cover; }
  .border { border: 1px solid; }
  .inline-flex { display: inline-flex; }
  .inline-block { display: inline-block; }

  /* --------------------------------------------
     1. HERO
  -------------------------------------------- */
  .hero {
    position: relative;
    min-height: 90vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: #0A0F1C;
  }
  .hero-bg-img {
    position: absolute; inset: 0;
  }
  .hero-bg-img img {
    width: 100%; height: 100%; object-fit: cover; opacity: .2;
  }
  .hero-bg-img::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to right, #0A0F1C, rgba(10,15,28,.8), rgba(10,15,28,.4));
  }
  .hero-orb1 {
    position: absolute; top: 80px; left: 40px;
    width: 500px; height: 500px;
    background: rgba(139,92,246,.2);
    border-radius: 50%; filter: blur(120px);
    animation: orb1 20s infinite linear;
  }
  .hero-orb2 {
    position: absolute; bottom: 80px; right: 40px;
    width: 400px; height: 400px;
    background: rgba(249,115,22,.15);
    border-radius: 50%; filter: blur(120px);
    animation: orb2 25s infinite linear;
  }
  @keyframes orb1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(100px,-50px) scale(1.2)} }
  @keyframes orb2 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-80px,60px) scale(1.3)} }
  .hero-inner {
    position: relative; z-index: 1;
    max-width: 1280px; margin: 0 auto;
    padding: 5rem 2rem;
    width: 100%;
    display: flex; align-items: center; justify-content: space-between;
  }
  .hero-content { max-width: 680px; }
  .hero-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .375rem 1rem;
    border-radius: 9999px;
    background: rgba(139,92,246,.1);
    border: 1px solid rgba(139,92,246,.2);
    color: #c4b5fd;
    font-size: .875rem; font-weight: 500;
    margin-bottom: 1.5rem;
  }
  .hero-badge .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #a78bfa;
    animation: pulse 2s infinite;
  }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
  .hero h1 {
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 800; color: #fff;
    line-height: 1.1; letter-spacing: -.02em;
    margin-bottom: 1.5rem;
  }
  .hero h1 .gradient-text {
    background: linear-gradient(to right, #a78bfa, #f472b6, #fb923c);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }
  .hero p {
    font-size: 1.125rem; color: #9ca3af;
    margin-bottom: 1.5rem; line-height: 1.7; max-width: 480px;
  }
  .hero-pills { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem; }
  .pill-green {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1rem; border-radius: 9999px;
    background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2);
    color: #86efac; font-size: .875rem; font-weight: 500;
  }
  .pill-violet {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1rem; border-radius: 9999px;
    background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.2);
    color: #c4b5fd; font-size: .875rem; font-weight: 500;
  }
  .pill-green .dot2, .pill-violet .dot2 {
    width: 8px; height: 8px; border-radius: 50%;
  }
  .pill-green .dot2 { background: #4ade80; }
  .pill-violet .dot2 { background: #a78bfa; }
  .hero-cta { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 3rem; }
  .btn-primary {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .875rem 2rem; border-radius: .75rem;
    background: #7c3aed; color: #fff;
    font-size: 1.125rem; font-weight: 600;
    border: none; cursor: pointer;
    transition: background .2s;
  }
  .btn-primary:hover { background: #6d28d9; }
  .btn-orange {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .875rem 2rem; border-radius: .75rem;
    background: linear-gradient(to right, #f97316, #ec4899); color: #fff;
    font-size: 1.125rem; font-weight: 600;
    border: none; cursor: pointer;
    box-shadow: 0 8px 20px rgba(249,115,22,.3);
    transition: opacity .2s;
  }
  .btn-orange:hover { opacity: .9; }
  .hero-social-proof {
    display: flex; align-items: center; gap: 2rem;
    color: #6b7280; font-size: .875rem;
  }
  .avatar-stack { display: flex; }
  .avatar-stack img {
    width: 32px; height: 32px; border-radius: 50%;
    border: 2px solid #0A0F1C; object-fit: cover;
    margin-left: -8px;
  }
  .avatar-stack img:first-child { margin-left: 0; }
  .hero-divider { width: 1px; height: 16px; background: #374151; }
  /* Floating card */
  .hero-card-wrap {
    display: none;
    position: relative;
  }
  @media(min-width:1024px){ .hero-card-wrap { display: block; } }
  .hero-card-main {
    width: 288px; border-radius: 1rem; overflow: hidden;
    background: rgba(255,255,255,.05); backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,.1);
    box-shadow: 0 25px 60px rgba(0,0,0,.4);
    animation: float1 4s ease-in-out infinite;
  }
  .hero-card-main img { width: 100%; height: 160px; object-fit: cover; }
  .hero-card-main-body { padding: 1rem;}
  .hero-card-main-body p { color: #fff; font-weight: 600; line-height: 0.6 !important; }
  .hero-card-main-body .meta { color: #9ca3af; font-size: .875rem; margin-top: .25rem; }
  .hero-card-main-body .row { display: flex; justify-content: space-between; align-items: center; margin-top: .75rem; }
  .hero-card-main-body .price { color: #a78bfa; font-weight: 600; }
  .hero-card-main-body .tag {
    font-size: .75rem; color: #fb923c;
    background: rgba(251,146,60,.1); padding: .25rem .625rem; border-radius: 9999px;
  }
  .hero-card-mini {
    position: absolute; bottom: -64px; left: -80px;
    width: 240px; border-radius: 1rem; overflow: hidden;
    background: rgba(255,255,255,.05); backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,.1);
    box-shadow: 0 20px 40px rgba(0,0,0,.3);
    animation: float2 5s ease-in-out infinite;
  }
  .hero-card-mini-body { padding: 1rem; display: flex; align-items: center; gap: .75rem; }
  .hero-card-icon {
    width: 48px; height: 48px; border-radius: .75rem;
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
  }
  .hero-card-mini-body .title { color: #fff; font-weight: 500; font-size: .875rem; }
  .hero-card-mini-body .sub { color: #9ca3af; font-size: .75rem; }
  @keyframes float1 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  @keyframes float2 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(10px)} }

  /* --------------------------------------------
     2. FEATURED EVENTS
  -------------------------------------------- */
  .section-events { padding: 5rem 0; background: #f9fafb; }
  .section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; }
  .section-header h2 { font-size: 1.875rem; font-weight: 700; color: #111827; }
  .section-header p { color: #6b7280; margin-top: .5rem; }
  .btn-ghost {
    display: inline-flex; align-items: center; gap: .25rem;
    color: #7c3aed; font-weight: 500;
    background: none; border: none; cursor: pointer; font-size: 1rem;
  }
  .events-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
  .event-card {
    display: block;
    border-radius: 1rem; overflow: hidden;
    background: #fff; border: 1px solid #f3f4f6;
    transition: box-shadow .3s, transform .3s;
  }
  .event-card:hover { box-shadow: 0 20px 40px rgba(139,92,246,.08); transform: translateY(-4px); }
  .event-card-img { position: relative; aspect-ratio: 16/10; overflow: hidden; }
  .event-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s; }
  .event-card:hover .event-card-img img { transform: scale(1.05); }
  .badge-sold { position: absolute; top: .75rem; left: .75rem; background: #ef4444; color: #fff; font-size: .75rem; font-weight: 700; padding: .25rem .75rem; border-radius: 9999px; }
  .badge-featured { position: absolute; top: .75rem; right: .75rem; background: linear-gradient(to right, #f97316, #ec4899); color: #fff; font-size: .75rem; font-weight: 700; padding: .25rem .75rem; border-radius: 9999px; }
  .event-card-body { padding: 1.25rem; }
  .event-card-meta { display: flex; gap: 1rem; color: #6b7280; font-size: .875rem; margin-bottom: .5rem; }
  .event-card-title { font-weight: 600; font-size: 1.125rem; color: #111827; margin-bottom: .5rem; line-height: 1.4; }
  .event-card-venue { font-size: .875rem; color: #6b7280; margin-bottom: .75rem; }
  .event-card-footer { display: flex; justify-content: space-between; align-items: center; }
  .event-price { color: #7c3aed; font-weight: 600; }
  .event-org { font-size: .75rem; color: #9ca3af; }

  /* --------------------------------------------
     3. EASY BOOKING
  -------------------------------------------- */
  .section-booking { padding: 2rem 0 2.5rem; background: #fff; }
  .pill-label {
    display: inline-block; padding: .375rem 1rem; border-radius: 9999px;
    background: #ede9fe; color: #6d28d9; font-size: .875rem; font-weight: 600;
    margin-bottom: 1rem;
  }
  .section-title { font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; color: #111827; margin-bottom: 1rem; }
  .gradient-span { background: linear-gradient(to right, #7c3aed, #db2777); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .section-sub { font-size: 1.125rem; color: #6b7280; max-width: 480px; margin: 0 auto; }
  .steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; align-items: stretch; }
  .step-wrap { display: flex; align-items: stretch; }
  .step-card {
    flex: 1; border-radius: 1.5rem; overflow: hidden;
    border: 1px solid #f3f4f6; background: #fff;
    transition: box-shadow .4s, transform .4s; margin: 0 .75rem;
  }
  .step-card:hover { box-shadow: 0 25px 50px rgba(139,92,246,.1); transform: translateY(-4px); }
  .step-img { position: relative; aspect-ratio: 4/3; overflow: hidden; }
  .step-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s; }
  .step-card:hover .step-img img { transform: scale(1.05); }
  .step-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,.6), transparent); }
  .step-num {
    position: absolute; top: 1rem; left: 1rem; z-index: 1;
    width: 48px; height: 48px; border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 900; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.3);
  }
  .step-num-1 { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
  .step-num-2 { background: linear-gradient(135deg, #ec4899, #e11d48); }
  .step-num-3 { background: linear-gradient(135deg, #f97316, #d97706); }
  .step-label {
    position: absolute; bottom: 1rem; left: 1rem; right: 1rem; z-index: 1;
  }
  .step-label span {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .375rem .75rem; border-radius: 9999px;
    background: rgba(255,255,255,.9); color: #1f2937;
    font-size: .75rem; font-weight: 600; backdrop-filter: blur(4px);
  }
  .step-body { padding: 1.5rem; }
  .step-body h3 { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: .5rem; }
  .step-body p { font-size: .875rem; color: #6b7280; line-height: 1.6; }
  .step-arrow {
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; z-index: 10; margin: 0 -.25rem;
  }
  .step-arrow span {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,.2);
  }

  /* --------------------------------------------
     4. CATEGORY BAR
  -------------------------------------------- */
  .section-cats { padding: 2rem 0 2.5rem; background: #fff; }
  .cats-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 1.5rem; }
  .cat-item { display: flex; flex-direction: column; align-items: center; gap: .75rem; cursor: pointer; }
  .cat-icon {
    width: 96px; height: 96px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 20px rgba(0,0,0,.15);
    transition: transform .3s;
  }
  .cat-item:hover .cat-icon { transform: scale(1.1); }
  .cat-icon svg { width: 44px; height: 44px; color: #fff; }
  .cat-name { font-size: .875rem; font-weight: 600; color: #374151; text-align: center; }
  .bg-violet { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
  .bg-pink { background: linear-gradient(135deg, #ec4899, #e11d48); }
  .bg-yellow { background: linear-gradient(135deg, #eab308, #ea580c); }
  .bg-green { background: linear-gradient(135deg, #22c55e, #10b981); }
  .bg-blue { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
  .bg-orange { background: linear-gradient(135deg, #f97316, #dc2626); }
  .bg-teal { background: linear-gradient(135deg, #14b8a6, #22c55e); }
  .bg-slate { background: linear-gradient(135deg, #64748b, #4b5563); }

  /* --------------------------------------------
     5. TRUST BADGES
  -------------------------------------------- */
  .section-trust { padding: 4rem 0; background: #f9fafb; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; }
  .badges-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 1.5rem; }
  .badge-item { text-align: center; }
  .badge-icon-wrap {
    display: inline-flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; border-radius: .75rem;
    background: #fff; border: 1px solid #e5e7eb; margin-bottom: .75rem;
  }
  .badge-icon-wrap svg { width: 20px; height: 20px; color: #7c3aed; }
  .badge-item .title { font-size: .875rem; font-weight: 600; color: #111827; }
  .badge-item .desc { font-size: .75rem; color: #6b7280; margin-top: .125rem; }
  .trust-logos { display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; align-items: center; margin-top: 3rem; opacity: .4; filter: grayscale(1); }
  .trust-logos img { height: 24px; object-fit: contain; }

  /* --------------------------------------------
     6. ORGANISER BENEFITS (dark)
  -------------------------------------------- */
  .section-org-benefits { padding: 4rem 0; background: linear-gradient(135deg, #0A0F1C, #111827); }
  .pill-orange {
    display: inline-block; padding: .375rem 1rem; border-radius: 9999px;
    background: rgba(249,115,22,.1); border: 1px solid rgba(249,115,22,.2);
    color: #fdba74; font-size: .875rem; font-weight: 600; margin-bottom: 1rem;
  }
  .org-benefits-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
  .org-benefit-card {
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    border-radius: 1rem; padding: 1.5rem;
    transition: background .3s;
  }
  .org-benefit-card:hover { background: rgba(255,255,255,.1); }
  .org-benefit-icon {
    width: 56px; height: 56px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.2);
  }
  .org-benefit-icon svg { width: 28px; height: 28px; color: #fff; }
  .org-benefit-stat { font-size: 1.875rem; font-weight: 900; color: #fff; margin-bottom: .5rem; }
  .org-benefit-title { font-size: 1.125rem; font-weight: 700; color: #fff; margin-bottom: .5rem; }
  .org-benefit-desc { font-size: .875rem; color: #9ca3af; line-height: 1.6; }
  .org-benefits-cta { text-align: center; margin-top: 2.5rem; }
  .btn-orange-lg {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: 1rem 2.5rem; border-radius: .75rem;
    background: linear-gradient(to right, #f97316, #ec4899); color: #fff;
    font-size: 1.125rem; font-weight: 600; border: none; cursor: pointer;
    box-shadow: 0 8px 24px rgba(249,115,22,.2);
  }
  .org-benefits-cta .note { color: #6b7280; font-size: .875rem; margin-top: .75rem; }

  /* --------------------------------------------
     7. WHY CHOOSE US
  -------------------------------------------- */
  .section-why { padding: 6rem 0; background: linear-gradient(135deg, #f5f3ff, #fff, #fff7ed); position: relative; }
  .why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
  .why-card {
    border-radius: 1rem; padding: 2rem; border: 2px solid;
    transition: transform .3s;
  }
  .why-card:hover { transform: translateY(-4px); }
  .why-card.highlight {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border-color: transparent; color: #fff;
    box-shadow: 0 20px 40px rgba(139,92,246,.2);
    position: relative;
  }
  .why-card.normal { background: #fff; border-color: #f3f4f6; }
  .why-card.normal:hover { box-shadow: 0 12px 24px rgba(139,92,246,.06); }
  .why-free-badge {
    position: absolute; top: -12px; right: -12px;
    background: #f97316; color: #fff; font-size: .75rem; font-weight: 700;
    padding: .25rem .75rem; border-radius: 9999px; box-shadow: 0 4px 12px rgba(249,115,22,.3);
  }
  .why-icon {
    width: 56px; height: 56px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;
  }
  .why-icon.light { background: #ede9fe; }
  .why-icon.light:hover { background: #ddd6fe; }
  .why-icon.dark { background: rgba(255,255,255,.2); }
  .why-icon svg { width: 28px; height: 28px; }
  .why-icon.light svg { color: #7c3aed; }
  .why-icon.dark svg { color: #fff; }
  .why-title { font-size: 1.25rem; font-weight: 700; margin-bottom: .75rem; }
  .why-card.highlight .why-title { color: #fff; }
  .why-card.normal .why-title { color: #111827; }
  .why-desc { line-height: 1.6; }
  .why-card.highlight .why-desc { color: rgba(255,255,255,.9); }
  .why-card.normal .why-desc { color: #4b5563; }
  .why-avatars { margin-top: 3rem; text-align: center; }
  .why-avatars-inner {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.8); backdrop-filter: blur(4px);
    padding: .75rem 1.5rem; border-radius: 9999px;
    border: 1px solid #ddd6fe; box-shadow: 0 4px 12px rgba(0,0,0,.08);
  }
  .why-avatars-inner img { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; object-fit: cover; }
  .why-avatars-inner img:first-child { margin-left: 0; }
  .why-avatars-inner span { font-size: .875rem; font-weight: 500; color: #374151; margin-left: .5rem; }

  /* --------------------------------------------
     8. HOW IT WORKS
  -------------------------------------------- */
  .section-hiw { padding: 6rem 0; background: #fff; }
  .hiw-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;
    position: relative;
  }
  .hiw-grid::before {
    content: '';
    position: absolute; top: 40px; left: 0; right: 0; height: 2px;
    background: linear-gradient(to right, #ddd6fe, #fbcfe8, #fed7aa);
  }
  .hiw-step { text-align: center; position: relative; }
  .hiw-icon-wrap {
    position: relative; display: inline-flex; align-items: center; justify-content: center;
    width: 80px; height: 80px; border-radius: 1rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(139,92,246,.3);
  }
  .hiw-icon-wrap svg { width: 36px; height: 36px; color: #fff; }
  .hiw-num {
    position: absolute; top: -8px; right: -8px;
    width: 32px; height: 32px; border-radius: 50%;
    background: #f97316; color: #fff; font-size: .875rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(249,115,22,.3);
  }
  .hiw-title { font-size: 1.125rem; font-weight: 700; color: #111827; margin-bottom: .5rem; }
  .hiw-desc { font-size: .875rem; color: #4b5563; line-height: 1.6; margin-bottom: .75rem; }
  .hiw-tag {
    display: inline-block; padding: .25rem .75rem; border-radius: 9999px;
    background: #f5f3ff; color: #6d28d9; font-size: .75rem; font-weight: 600;
  }

  /* --------------------------------------------
     9. CTA BANNER (primary)
  -------------------------------------------- */
  .section-cta { padding: 4rem 0; background: linear-gradient(135deg, #fff7ed, #fff, #f5f3ff); }
  .cta-card {
    position: relative; overflow: hidden;
    background: linear-gradient(to right, #7c3aed, #f97316);
    border-radius: 1.5rem; padding: 3.5rem; text-align: center;
    box-shadow: 0 25px 60px rgba(0,0,0,.2);
  }
  .cta-card::before, .cta-card::after {
    content: ''; position: absolute; border-radius: 50%; filter: blur(60px);
  }
  .cta-card::before { top: 0; right: 0; width: 256px; height: 256px; background: rgba(255,255,255,.1); }
  .cta-card::after { bottom: 0; left: 0; width: 192px; height: 192px; background: rgba(255,255,255,.1); }
  .cta-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1rem; border-radius: 9999px;
    background: rgba(255,255,255,.2); backdrop-filter: blur(4px);
    color: #fff; font-size: .875rem; font-weight: 600; margin-bottom: 1rem;
  }
  .cta-card h2 { font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; color: #fff; margin-bottom: 1rem; }
  .cta-card p { color: rgba(255,255,255,.9); font-size: 1.125rem; margin-bottom: 2rem; max-width: 480px; margin-left: auto; margin-right: auto; }
  .btn-white {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: 1rem 2.5rem; border-radius: .75rem;
    background: #fff; color: #7c3aed;
    font-size: 1.125rem; font-weight: 600; border: none; cursor: pointer;
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
  }
  .cta-note { color: rgba(255,255,255,.7); font-size: .875rem; margin-top: 1rem; }

  /* --------------------------------------------
     10. STATS
  -------------------------------------------- */
  .section-stats { padding: 5rem 0; background: #fff; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; position: relative; overflow: hidden; }
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
  .stat-item { text-align: center; }
  .stat-icon-wrap {
    display: inline-flex; align-items: center; justify-content: center;
    width: 56px; height: 56px; border-radius: 1rem;
    background: #f9fafb; border: 1px solid #e5e7eb; margin-bottom: 1rem;
  }
  .stat-icon-wrap svg { width: 28px; height: 28px; }
  .stat-value { font-size: 2.25rem; font-weight: 700; color: #111827; margin-bottom: .25rem; }
  .stat-label { font-size: .875rem; color: #6b7280; }

  /* --------------------------------------------
     11. EVENTS HAPPENING SOON (dark)
  -------------------------------------------- */
  .section-soon { padding: 5rem 0; background: #0A0F1C; position: relative; overflow: hidden; }
  .section-soon-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; }
  .live-badge { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
  .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #fb923c; animation: pulse 2s infinite; }
  .live-label { color: #fb923c; font-size: .875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .1em; }
  .section-soon h2 { font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; color: #fff; }
  .section-soon .sub { color: #9ca3af; margin-top: .5rem; }
  .btn-outline-white {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .625rem 1.25rem; border-radius: .75rem;
    border: 1px solid rgba(255,255,255,.2); color: #fff;
    background: transparent; cursor: pointer; font-size: .875rem; font-weight: 500;
  }
  .btn-outline-white:hover { background: rgba(255,255,255,.1); }

  /* --------------------------------------------
     12. ORGANISER LOGOS (carousel static)
  -------------------------------------------- */
  .section-logos { padding: 5rem 0; background: #f9fafb; overflow: hidden; }
  .logos-marquee { overflow: hidden; width: 100%; }
  .logos-row {
    display: flex; gap: 1rem; width: max-content; margin-bottom: 1rem;
    animation: logos-scroll 30s linear infinite;
    will-change: transform;
  }
  @keyframes logos-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }
  .logo-card {
    flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    height: 64px; padding: 0 2rem; border-radius: .75rem;
    background: #fff; border: 1px solid #f3f4f6; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    min-width: 180px;
  }
  .logo-card img { height: 28px; max-width: 140px; object-fit: contain; opacity: .6; filter: grayscale(1); }
  .logo-card span { font-size: .875rem; font-weight: 600; color: #6b7280; white-space: nowrap; }

  /* --------------------------------------------
     13. CTA BANNER (secondary)
  -------------------------------------------- */
  .section-cta2 { padding: 3rem 0; background: linear-gradient(to right, #7c3aed, #8b5cf6, #ec4899); }
  .section-cta2 h3 { font-size: clamp(1.25rem, 3vw, 1.875rem); font-weight: 700; color: #fff; margin-bottom: .75rem; }
  .section-cta2 p { color: rgba(255,255,255,.9); font-size: 1.125rem; margin-bottom: 1.5rem; }

  /* --------------------------------------------
     14. TESTIMONIALS
  -------------------------------------------- */
  .section-testimonials { padding: 6rem 0; background: #fff; overflow: hidden; }
  .testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
  .testimonial-card {
    background: linear-gradient(135deg, #f9fafb, #fff);
    padding: 2rem; border-radius: 1rem;
    border: 1px solid #f3f4f6; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    position: relative; transition: box-shadow .2s;
  }
  .testimonial-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); }
  .quote-icon {
    position: absolute; top: 1.5rem; right: 1.5rem;
    width: 32px; height: 32px; color: #ddd6fe;
  }
  .t-avatar { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
  .t-avatar img { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid #ede9fe; }
  .t-name { font-weight: 600; color: #111827; }
  .t-role { font-size: .875rem; color: #6b7280; }
  .stars { display: flex; gap: 2px; margin-bottom: 1rem; }
  .star { width: 16px; height: 16px; color: #facc15; fill: #facc15; }
  .t-text { color: #4b5563; line-height: 1.6; margin-bottom: .75rem; }
  .t-event { font-size: .75rem; color: #9ca3af; font-style: italic; }

  /* --------------------------------------------
     15. ORGANISER CTA (bottom)
  -------------------------------------------- */
  .section-org-cta { padding: 6rem 0; background: #fff; }
  .org-cta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
  .org-cta-pill {
    display: inline-block; padding: .375rem 1rem; border-radius: 9999px;
    background: #fff7ed; color: #c2410c; font-size: .875rem; font-weight: 500; margin-bottom: 1.5rem;
  }
  .org-cta h2 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; color: #111827; line-height: 1.2; margin-bottom: 1.5rem; }
  .org-cta p { font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem; line-height: 1.7; }
  .org-benefits-list { list-style: none; padding: 0; margin-bottom: 2.5rem; display: flex; flex-direction: column; gap: .75rem; }
  .org-benefits-list li { display: flex; align-items: center; gap: .75rem; color: #374151; }
  .check-icon { width: 20px; height: 20px; color: #7c3aed; flex-shrink: 0; }
  .org-cta-img { position: relative; }
  .org-cta-img img { width: 100%; border-radius: 1.5rem; box-shadow: 0 25px 60px rgba(0,0,0,.15); }
  .stat-float {
    position: absolute; background: #fff; border-radius: 1rem;
    padding: 1.25rem; border: 1px solid #f3f4f6; box-shadow: 0 8px 24px rgba(0,0,0,.1);
  }
  .stat-float.bottom { bottom: -24px; left: -24px; display: flex; align-items: center; gap: 1rem; }
  .stat-float.top { top: -16px; right: -16px; }
  .stat-float-icon {
    width: 48px; height: 48px; border-radius: .75rem;
    background: linear-gradient(135deg, #4ade80, #10b981);
    display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
  }
  .stat-float .val { font-size: 1.5rem; font-weight: 700; color: #111827; }
  .stat-float .lbl { font-size: .875rem; color: #6b7280; }
  .stat-float.top .val { font-size: .9rem; font-weight: 700; }
  .stat-float.top .lbl { font-size: .75rem; }
  .btn-violet-lg {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: 1rem 2rem; border-radius: .75rem;
    background: #7c3aed; color: #fff;
    font-size: 1.125rem; font-weight: 600; border: none; cursor: pointer;
  }
  .btn-violet-lg:hover { background: #6d28d9; }

  /* --------------------------------------------
     Responsive
  -------------------------------------------- */
  @media(max-width: 1023px) {
    .hero-inner { flex-direction: column; align-items: flex-start; gap: 2.5rem; padding: 4rem 2rem; }
    .hero-content { max-width: 100%; }
    .hero-cta { flex-direction: column; align-items: flex-start; width: 100%; }
    .hero-cta a { width: 100%; justify-content: center; }
    .hero-social-proof { flex-wrap: wrap; gap: 1rem; }
    .hero-divider { display: none; }
    .events-grid, .steps-grid { grid-template-columns: 1fr 1fr; }
    .cats-grid { grid-template-columns: repeat(4, 1fr); }
    .badges-grid { grid-template-columns: repeat(3, 1fr); }
    .org-benefits-grid { grid-template-columns: repeat(2, 1fr); }
    .why-grid { grid-template-columns: 1fr 1fr; }
    .hiw-grid { grid-template-columns: 1fr 1fr; }
    .hiw-grid::before { display: none; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .testimonials-grid { grid-template-columns: 1fr 1fr; }
    .org-cta-grid { grid-template-columns: 1fr; }
    .step-arrow { display: none; }
  }
  @media(max-width: 640px) {
    .hero-inner { padding: 3.25rem 1.5rem; }
    .events-grid, .steps-grid, .why-grid, .hiw-grid, .org-benefits-grid, .testimonials-grid { grid-template-columns: 1fr; }
    .cats-grid { grid-template-columns: repeat(3, 1fr); }
    .badges-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .section-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .section-soon-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  }
  @media(max-width: 480px) {
    .hero h1 { font-size: clamp(2rem, 8vw, 2.75rem); }
    .hero p { font-size: 1rem; }
    .cats-grid { grid-template-columns: repeat(2, 1fr); }
    .badges-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr; }
    .stat-float { display: none; }
  }
</style>
@endsection

@section('content')

<!-- ---------------------------------------
     1. HERO
--------------------------------------- -->
<section class="hero">
  <div class="hero-bg-img">
    <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1600&h=900&fit=crop" alt="Concert crowd" />
  </div>
  <div class="hero-orb1"></div>
  <div class="hero-orb2"></div>

  <div class="hero-inner">
    <div class="hero-content">
      <div class="hero-badge">
        <span class="dot"></span>
        Your Ticket to Live Experiences
      </div>
      <h1>
        Find Events You Love.
        <span class="gradient-text"> Sell Tickets for Free.</span>
      </h1>
      <p>From concerts and festivals to comedy nights and conferences — find the best events near you and book tickets in seconds.</p>

      <div class="hero-pills">
        <div class="pill-green"><span class="dot2"></span> Secure &amp; Encrypted Booking</div>
        <div class="pill-violet"><span class="dot2"></span> Instant QR Ticket Delivery</div>
      </div>

      <div class="hero-cta">
        <a href="{{ route('events.index') }}" class="btn-primary">Explore Events <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        <a href="{{ route('organiser.register') }}" class="btn-orange">List Your Event — It&rsquo;s Free</a>
      </div>

      <div class="hero-social-proof">
        <div style="display:flex;align-items:center;gap:.5rem;">
          <div class="avatar-stack">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=40&h=40&fit=crop&crop=face" alt="" />
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" alt="" />
            <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=40&h=40&fit=crop&crop=face" alt="" />
          </div>
          <span>10K+ happy attendees</span>
        </div>
        <div class="hero-divider"></div>
        <span>500+ events hosted</span>
      </div>
    </div>

    <!-- Floating cards -->
    <div class="hero-card-wrap">
      <div class="hero-card-main">
        <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400&h=200&fit=crop" alt="Concert" />
        <div class="hero-card-main-body">
          <p>Summer Music Festival</p>
          <p class="meta">Mar 15 · Central Park</p>
          <div class="row">
            <span class="price">From {{ ticketly_money(49) }}</span>
            <span class="tag">Trending</span>
          </div>
        </div>
      </div>
      <div class="hero-card-mini">
        <div class="hero-card-mini-body">
          <div class="hero-card-icon">&#127925;</div>
          <div>
            <div class="title">Jazz Night Live</div>
            <div class="sub">Tonight · 8 PM</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     2. UPCOMING EVENTS
--------------------------------------- -->
<section class="section-events" id="events">
  <div class="container">
    <div class="section-header">
      <div>
        <h2>Upcoming Events</h2>
        <p>Don&apos;t miss out on the hottest events near you</p>
      </div>
      <a href="{{ route('events.index') }}" class="btn-ghost">View All <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    @php
      $upcomingEvents = $events->take(6);
    @endphp
    <div class="events-grid">
      @forelse($upcomingEvents as $event)
        <a href="{{ route('events.show', $event->slug) }}" class="event-card">
          <div class="event-card-img">
            <img src="{{ $event->banner_url ?? 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop' }}" alt="{{ $event->title }}" />
            @if($event->is_featured)
              <span class="badge-featured">Featured</span>
            @endif
          </div>
          <div class="event-card-body">
            <div class="event-card-title">{{ $event->title }}</div>
          <div class="event-card-meta">
  <span style="display:flex;align-items:center;gap:.4rem;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    {{ ticketly_format_date($event->starts_at) }}
  </span>
  
</div>
           
            <div class="event-card-venue">
              <span style="display:flex;align-items:center;gap:.4rem;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z"/><circle cx="12" cy="11" r="3"/></svg>
             {{ $event->city }}
            </span>
            </div>
            <div class="event-card-footer">
              <span class="event-price">
                @php
                  $minPrice = $event->lowest_price;
                  $maxPrice = $event->highest_price ?? $minPrice;
                @endphp
                @if($minPrice <= 0 && $maxPrice <= 0)
                  Free
                @elseif($minPrice <= 0 && $maxPrice > 0)
                  Free - {{ ticketly_currency_symbol() . number_format($maxPrice, 0) }}
                @elseif($maxPrice > $minPrice)
                  From {{ ticketly_currency_symbol() . number_format($minPrice, 0) }} - {{ ticketly_currency_symbol() . number_format($maxPrice, 0) }}
                @else
                  From {{ ticketly_currency_symbol() . number_format($minPrice, 0) }}
                @endif
              </span>
              @if($event->organiser)
                <span class="event-org">by {{ $event->organiser->company_name ?? $event->organiser->name }}</span>
              @endif
            </div>
          </div>
        </a>
      @empty
        <div class="event-card" style="grid-column:1 / -1; border-style:dashed; text-align:center; padding:2.5rem;">
          <div class="event-card-body" style="padding:0;">
            <div class="event-card-title" style="margin-bottom:.5rem;">No upcoming events yet</div>
            <div class="event-card-venue" style="margin-bottom:1rem;">Check back soon for new listings or browse all events.</div>
            <a href="{{ route('events.index') }}" class="btn-ghost" style="display:inline-flex;align-items:center;gap:.25rem;">
              Browse Events
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</section>

<!-- ---------------------------------------
     3. EASY BOOKING (1 · 2 · 3)
--------------------------------------- -->
<section class="section-booking">
  <div class="container">
    <div class="text-center" style="margin-bottom:2.5rem;">
      <span class="pill-label">Super Simple</span>
      <!-- in span tag .(dot) dont show bottom show in middle of text -->
      <h2 class="section-title">Booking is as easy as <span class="gradient-span">1 · 2 · 3</span></h2>
      <p class="section-sub">From discovery to door entry seamlessly fast, fully secure.</p>
    </div>
    <div class="steps-grid">
      <div class="step-wrap">
        <div class="step-card">
          <div class="step-img">
            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=400&h=280&fit=crop" alt="Browse" />
            <div class="step-num step-num-1">1</div>
            <div class="step-label"><span>&#128269; Find what you love</span></div>
          </div>
          <div class="step-body">
            <h3>Browse</h3>
            <p>Discover thousands of events near you — concerts, festivals, comedy shows, and more. Filter by city, date, or category.</p>
          </div>
        </div>
        <div class="step-arrow"><span>?</span></div>
      </div>
      <div class="step-wrap">
        <div class="step-card">
          <div class="step-img">
            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=280&fit=crop" alt="Book" />
            <div class="step-num step-num-2">2</div>
            <div class="step-label"><span>&#128179; Checkout in 60 seconds</span></div>
          </div>
          <div class="step-body">
            <h3>Book</h3>
            <p>Select your tickets, choose your seats, and checkout securely in under 60 seconds. SSL-encrypted &amp; PCI compliant.</p>
          </div>
        </div>
        <div class="step-arrow"><span>?</span></div>
      </div>
      <div class="step-wrap">
        <div class="step-card">
          <div class="step-img">
            <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&h=280&fit=crop" alt="QR Code" />
            <div class="step-num step-num-3">3</div>
            <div class="step-label"><span>&#9993; Instant email delivery</span></div>
          </div>
          <div class="step-body">
            <h3>Get QR Code</h3>
            <p>Your QR ticket lands in your inbox instantly. Scan it at the door — no printing needed. It&apos;s that simple.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     4. BROWSE BY CATEGORY
--------------------------------------- -->
<section class="section-cats">
  <div class="container">
    <div class="text-center" style="margin-bottom:2.5rem;">
      <h2 class="section-title" style="margin-bottom:.5rem;">Browse by Category</h2>
      <p style="color:#6b7280;font-size:1.125rem;">Find events that match your vibe</p>
    </div>
    @php
      $catMeta = [
        'Music' => [
          'class' => 'bg-violet',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
        ],
        'Nightlife' => [
          'class' => 'bg-pink',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.5 2l-1.5 1.5M12 2a10 10 0 1 0 10 10"/><path d="M12 8v4l3 3"/></svg>',
        ],
        'Comedy' => [
          'class' => 'bg-yellow',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/></svg>',
        ],
        'Sports' => [
          'class' => 'bg-green',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 21l4-4 4 4M12 3v14M3 8l9-5 9 5"/></svg>',
        ],
        'Arts' => [
          'class' => 'bg-blue',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
        ],
        'Food & Drink' => [
          'class' => 'bg-orange',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>',
        ],
        'Wellness' => [
          'class' => 'bg-teal',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        ],
        'Business' => [
          'class' => 'bg-slate',
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        ],
      ];
    @endphp
    <div class="cats-grid">
      @foreach(\App\Models\Event::CATEGORIES as $cat)
        @php
          $meta = $catMeta[$cat] ?? ['class' => 'bg-slate', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>'];
        @endphp
        <a class="cat-item" href="{{ route('events.index', ['category' => $cat]) }}">
          <div class="cat-icon {{ $meta['class'] }}">
            {!! $meta['icon'] !!}
          </div>
          <span class="cat-name">{{ $cat }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>

<!-- ---------------------------------------
     5. TRUST BADGES
--------------------------------------- -->
<section class="section-trust">
  <div class="container">
    <div class="text-center" style="margin-bottom:2.5rem;">
      <h2 class="section-title">Bank-Level Security &amp; Compliance</h2>
      <p style="color:#6b7280;font-size:1.125rem;max-width:480px;margin:.75rem auto 0;">Your data and payments are protected by industry-leading security standards</p>
    </div>
    <div class="badges-grid">
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <div class="title">SSL Encrypted</div><div class="desc">256-bit encryption</div>
      </div>
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <div class="title">PCI Compliant</div><div class="desc">Secure payments</div>
      </div>
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg></div>
        <div class="title">Trusted Payments</div><div class="desc">Stripe &amp; PayPal</div>
      </div>
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
        <div class="title">Instant Payouts</div><div class="desc">Same-day transfers</div>
      </div>
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg></div>
        <div class="title">ISO Certified</div><div class="desc">Quality assured</div>
      </div>
      <div class="badge-item">
        <div class="badge-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="title">GDPR Compliant</div><div class="desc">Data protected</div>
      </div>
    </div>
    <div class="trust-logos">
      <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" />
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a4/Paypal_2014_logo.png" alt="PayPal" style="height:20px;" />
      <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" style="height:20px;" />
      <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" style="height:32px;" />
    </div>
  </div>
</section>

<!-- ---------------------------------------
     6. ORGANISER BENEFITS (dark)
--------------------------------------- -->
<section class="section-org-benefits">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem;">
      <span class="pill-orange">For Event Organisers</span>
      <h2 class="section-title" style="color:#fff;">Why Organisers Choose Ticketly</h2>
      <p style="color:#9ca3af;font-size:1.125rem;max-width:580px;margin:.75rem auto 0;">We don&apos;t just sell tickets — we grow your audience, amplify your brand, and help you sell out every time.</p>
    </div>
    <div class="org-benefits-grid">
      <div class="org-benefit-card">
        <div class="org-benefit-icon bg-violet"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div class="org-benefit-stat">20%</div>
        <div class="org-benefit-title">Sell 20% More Tickets</div>
        <p class="org-benefit-desc">Our smart recommendation engine and SEO-optimised event pages put your event in front of the right audience.</p>
      </div>
      <div class="org-benefit-card">
        <div class="org-benefit-icon bg-orange"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg></div>
        <div class="org-benefit-stat">FREE</div>
        <div class="org-benefit-title">We Promote You Online — Free</div>
        <p class="org-benefit-desc">Every event gets featured in our weekly newsletter, push notifications, and our homepage — at zero cost.</p>
      </div>
      <div class="org-benefit-card">
        <div class="org-benefit-icon bg-pink"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="org-benefit-stat">100+</div>
        <div class="org-benefit-title">Influencer Connections</div>
        <p class="org-benefit-desc">We connect you with 100+ vetted influencers in music, sports, nightlife, and lifestyle.</p>
      </div>
      <div class="org-benefit-card">
        <div class="org-benefit-icon bg-blue"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></div>
        <div class="org-benefit-stat">100K+</div>
        <div class="org-benefit-title">Free Social Media Promotion</div>
        <p class="org-benefit-desc">Your event is shared across our social channels with 100K+ engaged followers. Instagram, TikTok, Facebook.</p>
      </div>
    </div>
    <div class="org-benefits-cta">
      <a href="{{ route('organiser.register') }}" class="btn-orange-lg">Start Listing for Free <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      <p class="note">No credit card · No platform fees · Setup in 5 minutes</p>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     7. WHY CHOOSE US
--------------------------------------- -->
<section class="section-why">
  <div class="container">
    <div class="text-center" style="margin-bottom:4rem;">
      <span class="pill-label">Why Ticketly?</span>
      <h2 class="section-title">The Modern Platform Built for Your Success</h2>
      <p style="color:#4b5563;font-size:1.125rem;max-width:580px;margin:.75rem auto 0;">We remove every barrier between you and a sold-out event. No fees, no friction, just results.</p>
    </div>
    <div class="why-grid">
      <div class="why-card highlight">
        <div class="why-free-badge">FREE</div>
        <div class="why-icon dark"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <h3 class="why-title">100% Free for Organisers</h3>
        <p class="why-desc">Zero platform fees. Zero hidden charges. Keep every dollar you earn. We only charge ticket buyers a small service fee.</p>
      </div>
      <div class="why-card highlight">
        <div class="why-free-badge">FREE</div>
        <div class="why-icon dark"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <h3 class="why-title">Free Social Media Promotion</h3>
        <p class="why-desc">Your event gets shared across our social channels (100K+ followers) at no extra cost. Instant exposure to a massive audience.</p>
      </div>
      <div class="why-card highlight">
        <div class="why-free-badge">FREE</div>
        <div class="why-icon dark"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3 class="why-title">Influencer Collaborations</h3>
        <p class="why-desc">We partner with top influencers in music, comedy, sports, and lifestyle to promote select events. Get featured for free.</p>
      </div>
      <div class="why-card normal">
        <div class="why-icon light"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div>
        <h3 class="why-title">Easy &amp; Intuitive Interface</h3>
        <p class="why-desc">Create beautiful event pages in minutes. No tech skills needed. Our drag-and-drop tools make everything simple.</p>
      </div>
      <div class="why-card normal">
        <div class="why-icon light"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h3 class="why-title">Secure &amp; Trustworthy</h3>
        <p class="why-desc">Bank-level encryption, fraud protection, and secure QR ticketing. Your attendees&apos; data is always protected.</p>
      </div>
      <div class="why-card normal">
        <div class="why-icon light"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg></div>
        <h3 class="why-title">Instant Payouts</h3>
        <p class="why-desc">Get paid directly to your bank account within 24-48 hours of your event. Fast, reliable, and hassle-free.</p>
      </div>
    </div>
    <div class="why-avatars">
      <div class="why-avatars-inner">
        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=32&h=32&fit=crop&crop=face" alt="" />
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=32&h=32&fit=crop&crop=face" alt="" />
        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=32&h=32&fit=crop&crop=face" alt="" />
        <span>Join 10,000+ organisers already using Ticketly</span>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     8. HOW IT WORKS
--------------------------------------- -->
<section class="section-hiw">
  <div class="container-md">
    <div class="text-center" style="margin-bottom:4rem;">
      <h2 class="section-title">Get Started in 4 Simple Steps</h2>
      <p style="color:#6b7280;font-size:1.125rem;">From setup to sold-out in minutes</p>
    </div>
    <div class="hiw-grid">
      <div class="hiw-step">
        <div class="hiw-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <div class="hiw-num">1</div>
        </div>
        <h3 class="hiw-title">Create Your Event</h3>
        <p class="hiw-desc">Set up your event page in under 5 minutes. Add images, details, and ticket types with our simple interface.</p>
        <span class="hiw-tag">5 min</span>
      </div>
      <div class="hiw-step">
        <div class="hiw-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
          <div class="hiw-num">2</div>
        </div>
        <h3 class="hiw-title">Configure Tickets</h3>
        <p class="hiw-desc">Set pricing, capacity, and early-bird discounts. Multiple ticket tiers supported for VIP, general admission, and more.</p>
        <span class="hiw-tag">3 min</span>
      </div>
      <div class="hiw-step">
        <div class="hiw-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          <div class="hiw-num">3</div>
        </div>
        <h3 class="hiw-title">We Promote It</h3>
        <p class="hiw-desc">Your event goes live and gets promoted on our social channels, newsletters, and through influencer partnerships — all free.</p>
        <span class="hiw-tag">Instant</span>
      </div>
      <div class="hiw-step">
        <div class="hiw-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <div class="hiw-num">4</div>
        </div>
        <h3 class="hiw-title">Track &amp; Get Paid</h3>
        <p class="hiw-desc">Monitor real-time sales on your dashboard. Receive instant payouts to your bank account with zero fees.</p>
        <span class="hiw-tag">24-48h payouts</span>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     9. CTA BANNER (primary)
--------------------------------------- -->
<section class="section-cta">
  <div class="container-md">
    <div class="cta-card">
      <div style="position:relative;z-index:1;">
        <div class="cta-badge">&#10024; For Event Organisers</div>
        <h2>Ready to Sell Out Your Next Event?</h2>
        <p>Join 10,000+ organisers who list for FREE and keep 100% of ticket sales</p>
        <a href="{{ route('organiser.register') }}" class="btn-white">Get Started — It&apos;s Free <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        <p class="cta-note">· No credit card required · Setup in 5 minutes</p>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     10. STATS
--------------------------------------- -->
<section class="section-stats">
  <div class="container">
    <div class="text-center" style="margin-bottom:3.5rem;">
      <h2 class="section-title">Trusted by Thousands</h2>
      <p style="color:#6b7280;max-width:420px;margin:.75rem auto 0;">Join a growing community of event lovers and organisers who trust us to deliver unforgettable experiences.</p>
    </div>
    <div class="stats-grid">
      <div class="stat-item">
        <div class="stat-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg></div>
        <div class="stat-value">50K+</div><div class="stat-label">Tickets Sold</div>
      </div>
      <div class="stat-item">
        <div class="stat-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ec4899" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="stat-value">10K+</div><div class="stat-label">Happy Attendees</div>
      </div>
      <div class="stat-item">
        <div class="stat-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="stat-value">500+</div><div class="stat-label">Events Hosted</div>
      </div>
      <div class="stat-item">
        <div class="stat-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="#facc15" stroke="#facc15" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
        <div class="stat-value">4.9</div><div class="stat-label">Average Rating</div>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     11. EVENTS HAPPENING SOON (dark)
--------------------------------------- -->
<section class="section-soon">
  <div class="container">
    <div class="section-soon-header">
      <div>
        <div class="live-badge"><span class="live-dot"></span><span class="live-label">Live Now</span></div>
        <h2>Events Happening Soon!</h2>
        <p class="sub">Don&apos;t miss what&apos;s around the corner</p>
      </div>
      <a href="{{ route('events.index') }}" class="btn-outline-white">View All <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    @php
      $soonEvents = $events->slice(6, 3);
      if ($soonEvents->isEmpty()) {
        $soonEvents = $events->take(3);
      }
    @endphp
    <div class="events-grid">
      @forelse($soonEvents as $event)
        <a href="{{ route('events.show', $event->slug) }}" class="event-card">
          <div class="event-card-img">
            <img src="{{ $event->banner_url ?? 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=600&h=400&fit=crop' }}" alt="{{ $event->title }}" />
            @if($event->is_featured)
              <span class="badge-featured">Featured</span>
            @endif
          </div>
          <div class="event-card-body">
            <div class="event-card-title">{{ $event->title }}</div>
            <div class="event-card-meta">
  <span style="display:flex;align-items:center;gap:.4rem;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    {{ ticketly_format_date($event->starts_at) }}
  </span>
 
</div>
            
            <div class="event-card-venue"> <span style="display:flex;align-items:center;gap:.4rem;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z"/><circle cx="12" cy="11" r="3"/></svg>
    {{ $event->city }}
  </span></div>
            <div class="event-card-footer">
              <span class="event-price">
                @php
                  $minPrice = $event->lowest_price;
                @endphp
                @if($minPrice <= 0)
                  Free
                @else
                  From {{ ticketly_currency_symbol() . number_format($minPrice, 0) }}
                @endif
              </span>
              @if($event->organiser)
                <span class="event-org">by {{ $event->organiser->company_name ?? $event->organiser->name }}</span>
              @endif
            </div>
          </div>
        </a>
      @empty
        <div class="event-card" style="grid-column:1 / -1; background:rgba(255,255,255,.06); border:1px dashed rgba(255,255,255,.2); text-align:center; padding:2.5rem;">
          <div class="event-card-body" style="padding:0;">
            <div class="event-card-title" style="color:#fff;margin-bottom:.5rem;">No events happening soon</div>
            <div class="event-card-venue" style="color:#9ca3af;margin-bottom:1rem;">Check back later or explore all upcoming events.</div>
            <a href="{{ route('events.index') }}" class="btn-outline-white" style="display:inline-flex;align-items:center;gap:.25rem;">
              Browse Events
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</section>

<!-- ---------------------------------------
     12. ORGANISER LOGOS
--------------------------------------- -->
<section class="section-logos">
  <div class="container" style="margin-bottom:3rem;">
    <div class="text-center">
      <span style="display:inline-block;padding:.375rem 1rem;border-radius:9999px;background:#fff7ed;color:#c2410c;font-size:.875rem;font-weight:600;margin-bottom:1rem;">Event Organisers</span>
      <h2 class="section-title">Trusted by the World&apos;s Best Event Brands</h2>
      <p style="color:#6b7280;font-size:1.125rem;max-width:480px;margin:.75rem auto 0;">From intimate gigs to global festivals — leading organisers choose Ticketly to sell tickets and grow their audience.</p>
    </div>
  </div>
  <div class="logos-marquee">
    <div class="logos-row">
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/9/9d/Live_Nation_Entertainment_logo.svg" alt="Live Nation" /></div>
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/AEG_logo.svg" alt="AEG" /></div>
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/7/7d/Eventbrite_logo.svg" alt="Eventbrite" /></div>
      <div class="logo-card"><span>SFX Entertainment</span></div>
      <div class="logo-card"><span>Cirque du Soleil</span></div>
      <div class="logo-card"><span>MSG Entertainment</span></div>
      <div class="logo-card"><span>Coachella Valley</span></div>
      <div class="logo-card"><span>Glastonbury Festivals</span></div>
      <div class="logo-card"><span>Ultra Music Festival</span></div>
      <div class="logo-card"><span>Tomorrowland</span></div>
      <div class="logo-card"><span>Lollapalooza</span></div>
      <div class="logo-card"><span>Reading &amp; Leeds</span></div>
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/9/9d/Live_Nation_Entertainment_logo.svg" alt="Live Nation" /></div>
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/AEG_logo.svg" alt="AEG" /></div>
      <div class="logo-card"><img src="https://upload.wikimedia.org/wikipedia/commons/7/7d/Eventbrite_logo.svg" alt="Eventbrite" /></div>
      <div class="logo-card"><span>SFX Entertainment</span></div>
      <div class="logo-card"><span>Cirque du Soleil</span></div>
      <div class="logo-card"><span>MSG Entertainment</span></div>
      <div class="logo-card"><span>Coachella Valley</span></div>
      <div class="logo-card"><span>Glastonbury Festivals</span></div>
      <div class="logo-card"><span>Ultra Music Festival</span></div>
      <div class="logo-card"><span>Tomorrowland</span></div>
      <div class="logo-card"><span>Lollapalooza</span></div>
      <div class="logo-card"><span>Reading &amp; Leeds</span></div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     13. CTA BANNER (secondary)
--------------------------------------- -->
<section class="section-cta2">
  <div class="container-md text-center">
    <h3>List Your Event. 100% Free. Forever.</h3>
    <p>No platform fees. No hidden charges. Just pure success.</p>
    <a href="{{ route('organiser.register') }}" class="btn-white">Start Selling Tickets Free <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
  </div>
</section>

<!-- ---------------------------------------
     14. TESTIMONIALS
--------------------------------------- -->
<section class="section-testimonials">
  <div class="container">
    <div class="text-center" style="margin-bottom:4rem;">
      <h2 class="section-title">Trusted by Event Organisers Worldwide</h2>
      <p style="color:#6b7280;font-size:1.125rem;max-width:580px;margin:.75rem auto 0;">Join thousands of organisers who&apos;ve sold millions of tickets on our platform</p>
    </div>
    <div class="testimonials-grid">
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">Sarah Martinez</div><div class="t-role">Festival Organiser</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;Ticketly made selling 5,000 tickets a breeze. The dashboard is intuitive, and their team helped us every step of the way. Best of all — zero platform fees!&quot;</p>
        <div class="t-event">Summer Beats Festival</div>
      </div>
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">Marcus Thompson</div><div class="t-role">Comedy Club Owner</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;We&apos;ve tried other platforms, but Ticketly is hands down the best. Easy to use, secure payments, and their social media promotion brought us tons of new fans.&quot;</p>
        <div class="t-event">Laugh Factory</div>
      </div>
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">Jessica Chen</div><div class="t-role">Corporate Event Manager</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;Setting up our conference took less than 15 minutes. The analytics helped us understand our audience better. Can&apos;t recommend enough!&quot;</p>
        <div class="t-event">Tech Summit 2025</div>
      </div>
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">David Johnson</div><div class="t-role">Music Promoter</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;The influencer collaborations and social exposure were game-changers for us. Sold out every show in our series. Ticketly is the real deal.&quot;</p>
        <div class="t-event">Underground Beats Series</div>
      </div>
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">Amanda Rodriguez</div><div class="t-role">Food Festival Coordinator</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;Free to list, easy to manage, and instant payments. Ticketly saved us thousands in fees compared to other platforms. Absolutely love it!&quot;</p>
        <div class="t-event">Street Food Nation</div>
      </div>
      <div class="testimonial-card">
        <svg class="quote-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        <div class="t-avatar"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=80&h=80&fit=crop&crop=face" alt="" /><div><div class="t-name">Ryan Lee</div><div class="t-role">Nightclub Manager</div></div></div>
        <div class="stars">&#11088;&#11088;&#11088;&#11088;&#11088;</div>
        <p class="t-text">&quot;Security features are top-notch. QR codes, fraud protection, and smooth check-ins. Our attendees trust it, and so do we.&quot;</p>
        <div class="t-event">Neon Saturdays</div>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------
     15. ORGANISER CTA (bottom)
--------------------------------------- -->
<section class="section-org-cta">
  <div class="container">
    <div class="org-cta-grid">
      <div class="org-cta">
        <span class="org-cta-pill">For Event Organisers</span>
        <h2>Sell your events <span style="background:linear-gradient(to right,#7c3aed,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">for FREE</span></h2>
        <p>Whether you&apos;re hosting a small meetup or a massive festival, we give you everything you need to create, promote, and manage your events — all for FREE. Zero platform fees. Zero hidden charges.</p>
        <ul class="org-benefits-list">
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> List your events 100% FREE — zero platform fees</li>
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Free promotion on our social channels (100K+ followers)</li>
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Influencer collaborations to boost your reach</li>
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Secure payments — get paid within 24-48 hours</li>
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Real-time sales dashboard &amp; analytics</li>
          <li><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Easy-to-use interface — setup in minutes</li>
        </ul>
        <a href="{{ route('organiser.register') }}" class="btn-violet-lg">Start Selling Tickets <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
      <div class="org-cta-img">
        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=500&fit=crop" alt="Event organiser" />
        <div class="stat-float bottom" style="animation:float1 4s ease-in-out infinite;">
          <div class="stat-float-icon">&#128200;</div>
          <div><div class="val">98%</div><div class="lbl">Organiser Satisfaction</div></div>
        </div>
        <div class="stat-float top" style="animation:float2 5s ease-in-out infinite;">
          <div class="val">$2M+</div><div class="lbl">Revenue generated</div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection











