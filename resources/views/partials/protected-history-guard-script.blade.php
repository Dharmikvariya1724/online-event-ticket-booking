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

    function setItem(key, value) {
      try {
        localStorage.setItem(key, value);
      } catch (error) {
        // Ignore storage access failures.
      }
    }

    function removeItem(key) {
      try {
        localStorage.removeItem(key);
      } catch (error) {
        // Ignore storage access failures.
      }
    }

    function hidePage() {
      document.documentElement.setAttribute(hiddenAttr, '1');
    }

    function showPage() {
      document.documentElement.removeAttribute(hiddenAttr);
    }

    function publishAuthState() {
      setItem(authStateKey, pageAuthState);
      showPage();
    }

    function redirectToLogin() {
      hidePage();
      window.location.replace(getItem(logoutRedirectKey) || fallbackRedirect);
    }

    function reloadForFreshSession() {
      hidePage();
      window.location.reload();
    }

    function handleLogoutState() {
      var logoutState = getItem(logoutStateKey);
      if (logoutState !== pageAuthState) {
        return false;
      }

      var currentAuthState = getItem(authStateKey);
      if (currentAuthState && currentAuthState !== pageAuthState) {
        reloadForFreshSession();
        return true;
      }

      redirectToLogin();
      return true;
    }

    document.addEventListener('submit', function (event) {
      var form = event.target.closest('form[data-logout-guard]');
      if (!form) return;

      var formNamespace = form.getAttribute('data-logout-namespace') || namespace;
      var formState = form.getAttribute('data-logout-state') || pageAuthState;
      var formRedirect = form.getAttribute('data-logout-redirect') || fallbackRedirect;
      var formAuthStateKey = 'ticketly:' + formNamespace + ':auth-state';
      var formLogoutStateKey = 'ticketly:' + formNamespace + ':logout-state';
      var formLogoutRedirectKey = 'ticketly:' + formNamespace + ':logout-redirect';

      setItem(formLogoutStateKey, formState);
      setItem(formLogoutRedirectKey, formRedirect);
      removeItem(formAuthStateKey);
      hidePage();
    });

    window.addEventListener('storage', function (event) {
      if (event.key !== authStateKey && event.key !== logoutStateKey && event.key !== logoutRedirectKey) {
        return;
      }

      handleLogoutState();
    });

    window.addEventListener('pageshow', function () {
      if (handleLogoutState()) {
        return;
      }

      publishAuthState();
    });

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) {
        handleLogoutState();
      }
    });

    window.addEventListener('focus', handleLogoutState);
    window.addEventListener('pagehide', hidePage);
    window.addEventListener('beforeunload', hidePage);

    publishAuthState();
  })();
</script>
