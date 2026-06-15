<style>
  html[data-checkout-transition-hidden='1'] body {
    opacity: 0 !important;
    visibility: hidden !important;
  }
</style>
<script>
  (function () {
    var reservationToken = @json($reservationToken ?? null);
    var processingUrl = @json($processingUrl ?? null);
    var hiddenAttr = 'data-checkout-transition-hidden';

    if (!reservationToken) {
      return;
    }

    function getItem(key) {
      try {
        return sessionStorage.getItem(key) || '';
      } catch (error) {
        return '';
      }
    }

    function hidePage() {
      document.documentElement.setAttribute(hiddenAttr, '1');
    }

    var completedToken = getItem('ticketly:checkout-complete-token');
    var completedRedirect = getItem('ticketly:checkout-complete-redirect');
    if (completedToken === reservationToken && completedRedirect) {
      hidePage();
      window.location.replace(completedRedirect);
      return;
    }

    var activeToken = getItem('ticketly:checkout-active-token');
    var successRedirect = getItem('ticketly:checkout-success-url');
    if (processingUrl && activeToken === reservationToken && successRedirect) {
      hidePage();
      window.location.replace(successRedirect);
    }
  })();
</script>
