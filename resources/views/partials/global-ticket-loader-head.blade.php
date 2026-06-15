<style>
  :root {
    --ticket-loader-overlay: rgba(3, 7, 18, 0.76);
    --ticket-loader-panel: rgba(15, 23, 42, 0.9);
    --ticket-loader-panel-border: rgba(148, 163, 184, 0.18);
    --ticket-loader-text: #f8fafc;
    --ticket-loader-muted: #cbd5e1;
    --ticket-loader-ticket-start: #4f46e5;
    --ticket-loader-ticket-end: #ec4899;
    --ticket-loader-cut: #09111f;
  }

  #ticketly-global-loader {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.28s ease, visibility 0.28s ease;
  }

  #ticketly-global-loader::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at top, rgba(99, 102, 241, 0.28), transparent 42%),
      radial-gradient(circle at bottom, rgba(236, 72, 153, 0.2), transparent 38%),
      var(--ticket-loader-overlay);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  html[data-ticket-loader-visible='1'] #ticketly-global-loader {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .ticketly-loader-shell {
    position: relative;
    width: min(100%, 23rem);
    padding: 1.5rem 1.35rem 1.35rem;
    border: 1px solid var(--ticket-loader-panel-border);
    border-radius: 1.75rem;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.8));
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.38);
    text-align: center;
    overflow: hidden;
  }

  .ticketly-loader-shell::after {
    content: "";
    position: absolute;
    inset: -30% auto auto 50%;
    width: 14rem;
    height: 14rem;
    transform: translateX(-50%);
    background: radial-gradient(circle, rgba(129, 140, 248, 0.18), transparent 65%);
    pointer-events: none;
  }

  .ticketly-loader-ticket {
    position: relative;
    width: min(100%, 13rem);
    height: 7.25rem;
    margin: 0 auto 1.2rem;
    padding: 1rem 1.1rem;
    border-radius: 1.35rem;
    background: linear-gradient(135deg, var(--ticket-loader-ticket-start), var(--ticket-loader-ticket-end));
    color: #fff;
    box-shadow: 0 18px 38px rgba(79, 70, 229, 0.35);
    overflow: hidden;
    animation: ticketly-loader-float 1.8s ease-in-out infinite;
  }

  .ticketly-loader-ticket::before,
  .ticketly-loader-ticket::after {
    content: "";
    position: absolute;
    top: 50%;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 9999px;
    background: var(--ticket-loader-cut);
    transform: translateY(-50%);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
  }

  .ticketly-loader-ticket::before {
    left: -0.55rem;
  }

  .ticketly-loader-ticket::after {
    right: -0.55rem;
  }

  .ticketly-loader-ticket-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.85rem;
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    opacity: 0.92;
  }

  .ticketly-loader-dots {
    display: inline-flex;
    gap: 0.28rem;
  }

  .ticketly-loader-dots span {
    width: 0.34rem;
    height: 0.34rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.9);
    animation: ticketly-loader-pulse 1.1s ease-in-out infinite;
  }

  .ticketly-loader-dots span:nth-child(2) {
    animation-delay: 0.14s;
  }

  .ticketly-loader-dots span:nth-child(3) {
    animation-delay: 0.28s;
  }

  .ticketly-loader-divider {
    position: relative;
    height: 1px;
    margin-bottom: 0.85rem;
    background: repeating-linear-gradient(
      90deg,
      rgba(255, 255, 255, 0.45) 0,
      rgba(255, 255, 255, 0.45) 0.35rem,
      transparent 0.35rem,
      transparent 0.65rem
    );
    opacity: 0.72;
  }

  .ticketly-loader-ticket-body {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.9rem;
  }

  .ticketly-loader-seat {
    text-align: left;
  }

  .ticketly-loader-seat-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.58rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    opacity: 0.82;
  }

  .ticketly-loader-seat-value {
    display: block;
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: 0.06em;
  }

  .ticketly-loader-barcode {
    display: flex;
    align-items: flex-end;
    gap: 0.14rem;
    height: 2rem;
  }

  .ticketly-loader-barcode span {
    display: block;
    width: 0.18rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.9);
    animation: ticketly-loader-barcode 1.4s ease-in-out infinite;
  }

  .ticketly-loader-barcode span:nth-child(odd) {
    height: 1.8rem;
  }

  .ticketly-loader-barcode span:nth-child(even) {
    height: 1.15rem;
  }

  .ticketly-loader-barcode span:nth-child(3n) {
    height: 1.45rem;
  }

  .ticketly-loader-barcode span:nth-child(2) { animation-delay: 0.08s; }
  .ticketly-loader-barcode span:nth-child(3) { animation-delay: 0.16s; }
  .ticketly-loader-barcode span:nth-child(4) { animation-delay: 0.24s; }
  .ticketly-loader-barcode span:nth-child(5) { animation-delay: 0.32s; }
  .ticketly-loader-barcode span:nth-child(6) { animation-delay: 0.4s; }
  .ticketly-loader-barcode span:nth-child(7) { animation-delay: 0.48s; }

  .ticketly-loader-scan {
    position: absolute;
    top: 0.7rem;
    bottom: 0.7rem;
    width: 2.2rem;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transform: translateX(-140%);
    animation: ticketly-loader-scan 1.35s ease-in-out infinite;
    filter: blur(0.2px);
    opacity: 0.82;
  }

  .ticketly-loader-title {
    position: relative;
    margin: 0;
    color: var(--ticket-loader-text);
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.02em;
  }

  .ticketly-loader-copy {
    position: relative;
    margin: 0.42rem 0 0;
    color: var(--ticket-loader-muted);
    font-size: 0.92rem;
    line-height: 1.55;
  }

  @keyframes ticketly-loader-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
  }

  @keyframes ticketly-loader-scan {
    0% { transform: translateX(-140%); }
    100% { transform: translateX(700%); }
  }

  @keyframes ticketly-loader-pulse {
    0%, 100% { opacity: 0.38; transform: scale(0.88); }
    50% { opacity: 1; transform: scale(1); }
  }

  @keyframes ticketly-loader-barcode {
    0%, 100% { opacity: 0.72; transform: scaleY(0.9); }
    50% { opacity: 1; transform: scaleY(1); }
  }

  @media (max-width: 640px) {
    .ticketly-loader-shell {
      padding: 1.25rem 1rem 1.1rem;
      border-radius: 1.45rem;
    }

    .ticketly-loader-ticket {
      width: min(100%, 11.5rem);
      height: 6.7rem;
      margin-bottom: 1rem;
      padding: 0.92rem 0.95rem;
    }

    .ticketly-loader-title {
      font-size: 0.96rem;
    }

    .ticketly-loader-copy {
      font-size: 0.86rem;
    }
  }
</style>
<script>
  (function () {
    document.documentElement.setAttribute('data-ticket-loader-visible', '1');
  })();
</script>
