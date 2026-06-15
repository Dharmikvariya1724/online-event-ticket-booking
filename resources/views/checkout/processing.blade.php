@extends('layouts.app')
@section('title', 'Processing Payment...')

@section('head')
@include('partials.checkout-transition-guard-head', ['reservationToken' => $reservation->token])
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center px-4" role="main">
  <div class="text-center max-w-md" aria-live="polite" aria-atomic="true">
    <div class="w-20 h-20 mx-auto mb-8 rounded-full flex items-center justify-center" style="background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(139,92,246,0.2));border:2px solid rgba(99,102,241,0.4)">
      <svg class="w-10 h-10 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-extrabold text-white mb-2">Processing Your Payment</h1>
    <p class="text-gray-400 mb-2">Please wait, this usually takes a few seconds.</p>
    <p class="text-gray-600 text-sm">Do not close this tab.</p>
    <div id="status-msg" class="mt-6 text-sm text-gray-500" role="status"></div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const activeTokenKey = 'ticketly:checkout-active-token';
    const successUrlKey = 'ticketly:checkout-success-url';
    const completeTokenKey = 'ticketly:checkout-complete-token';
    const completeRedirectKey = 'ticketly:checkout-complete-redirect';
    const pollUrl = '{{ route('checkout.poll', $reservation->token) }}?attempt=1';
    const checkoutUrl = '{{ route('checkout.show', $reservation->token) }}';
    const fallbackEventUrl = '{{ route('events.show', $reservation->event->slug) }}';
    let attempts = 0;
    const maxAttempts = 30;

    function getItem(key) {
        try {
            return sessionStorage.getItem(key) || '';
        } catch (error) {
            return '';
        }
    }

    function setItem(key, value) {
        try {
            sessionStorage.setItem(key, value);
        } catch (error) {
            // Ignore storage access failures.
        }
    }

    function removeItem(key) {
        try {
            sessionStorage.removeItem(key);
        } catch (error) {
            // Ignore storage access failures.
        }
    }

    function clearCheckoutTransitionState() {
        if (getItem(activeTokenKey) === '{{ $reservation->token }}') {
            removeItem(activeTokenKey);
        }
        if (getItem(successUrlKey) === window.location.href) {
            removeItem(successUrlKey);
        }
        if (getItem(completeTokenKey) === '{{ $reservation->token }}') {
            removeItem(completeTokenKey);
        }
        removeItem(completeRedirectKey);
    }

    setItem(activeTokenKey, '{{ $reservation->token }}');
    setItem(successUrlKey, window.location.href);

    window.history.pushState({ checkoutProcessing: true }, '', window.location.href);
    window.addEventListener('popstate', function () {
        window.history.pushState({ checkoutProcessing: true }, '', window.location.href);
    });

    async function poll() {
        attempts++;
        try {
            const res  = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.status === 'paid') {
                setItem(completeTokenKey, '{{ $reservation->token }}');
                setItem(completeRedirectKey, data.redirect);
                document.getElementById('status-msg').textContent = 'Payment confirmed! Redirecting...';
                window.location.replace(data.redirect);
                return;
            }
            if (data.status === 'failed') {
                clearCheckoutTransitionState();
                document.getElementById('status-msg').textContent = 'Payment failed.';
                setTimeout(() => { window.location.replace(checkoutUrl); }, 2000);
                return;
            }
            if (data.status === 'expired') {
                clearCheckoutTransitionState();
                document.getElementById('status-msg').textContent = data.message || 'Your hold expired. Redirecting to event page...';
                setTimeout(() => {
                    window.location.replace(data.redirect_to || fallbackEventUrl);
                }, 1800);
                return;
            }
        } catch(e) { /* continue polling */ }

        if (attempts >= maxAttempts) {
            document.getElementById('status-msg').textContent = 'Taking longer than expected. We are still confirming your payment...';
            setTimeout(poll, 5000);
            return;
        }
        setTimeout(poll, 2000);
    }

    poll();
})();
</script>
@endsection
