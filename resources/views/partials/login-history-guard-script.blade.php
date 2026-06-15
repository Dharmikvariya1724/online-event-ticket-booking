<script>
  (function () {
    var namespace = @json($guardNamespace ?? null);

    if (!namespace) {
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

    function isLogoutLocked() {
      var logoutState = getItem(logoutStateKey);
      if (!logoutState) {
        return false;
      }

      var currentAuthState = getItem(authStateKey);
      return !currentAuthState || currentAuthState === logoutState;
    }

    function getRedirectUrl() {
      return getItem(logoutRedirectKey) || window.location.href;
    }

    function pinCurrentHistoryEntry() {
      try {
        window.history.pushState({ ticketlyLogoutGuard: namespace }, '', window.location.href);
      } catch (error) {
        // Ignore history API failures.
      }
    }

    if (!isLogoutLocked()) {
      return;
    }

    pinCurrentHistoryEntry();

    window.addEventListener('popstate', function () {
      if (!isLogoutLocked()) return;
      pinCurrentHistoryEntry();
      window.location.replace(getRedirectUrl());
    });

    window.addEventListener('pageshow', function (event) {
      if (!event.persisted || !isLogoutLocked()) return;
      pinCurrentHistoryEntry();
    });
  })();
</script>
