<style>
  html[data-protected-history-hidden='1'] body {
    opacity: 0 !important;
    visibility: hidden !important;
  }

  html[data-protected-history-hidden='1'][data-ticket-loader-visible='1'] body {
    opacity: 1 !important;
    visibility: visible !important;
  }

  html[data-protected-history-hidden='1'][data-ticket-loader-visible='1'] #ticketly-global-loader {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
  }
</style>
<script>
  (function () {
    var namespace = @json($guardNamespace ?? null);
    var pageAuthState = @json($guardState ?? null);
    var fallbackRedirect = @json($fallbackRedirect ?? route('organiser.login'));
    var hiddenAttr = 'data-protected-history-hidden';

    if (!namespace || !pageAuthState) {
      return;
    }

    var authStateKey = 'ticketly:' + namespace + ':auth-state';
    var logoutStateKey = 'ticketly:' + namespace + ':logout-state';
    var logoutRedirectKey = 'ticketly:' + namespace + ':logout-redirect';

    function getItem(key) {
      try {
        return localStorage.getItem(key) || '';
      } catch (error) {
        return '';
      }
    }

    function hidePage() {
      document.documentElement.setAttribute(hiddenAttr, '1');
    }

    var logoutState = getItem(logoutStateKey);
    if (logoutState !== pageAuthState) {
      return;
    }

    hidePage();

    var currentAuthState = getItem(authStateKey);
    if (currentAuthState && currentAuthState !== pageAuthState) {
      window.location.reload();
      return;
    }

    window.location.replace(getItem(logoutRedirectKey) || fallbackRedirect);
  })();
</script>
