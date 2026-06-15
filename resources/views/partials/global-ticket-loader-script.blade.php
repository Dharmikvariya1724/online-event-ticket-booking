<script>
  (function () {
    var root = document.documentElement;
    var minimumVisibleMs = 240;
    var bootTime = Date.now();
    var hideTimer = null;
    var transitionLocked = false;
    var nativeSubmit = null;

    function showLoader() {
      root.setAttribute('data-ticket-loader-visible', '1');
    }

    function hideLoader(force) {
      var elapsed = Date.now() - bootTime;
      var remaining = force ? 0 : Math.max(minimumVisibleMs - elapsed, 0);

      if (hideTimer) {
        window.clearTimeout(hideTimer);
      }

      hideTimer = window.setTimeout(function () {
        root.removeAttribute('data-ticket-loader-visible');
      }, remaining);
    }

    function isModifiedClick(event) {
      return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
    }

    function shouldHandleLink(link, event) {
      if (!link || event.defaultPrevented || isModifiedClick(event)) {
        return false;
      }

      if (link.hasAttribute('download') || (link.getAttribute('target') && link.getAttribute('target') !== '_self')) {
        return false;
      }

      if (link.hasAttribute('data-no-loader')) {
        return false;
      }

      var href = link.getAttribute('href') || '';
      if (!href || href.charAt(0) === '#') {
        return false;
      }

      if (/^(mailto:|tel:|javascript:)/i.test(href)) {
        return false;
      }

      try {
        var url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) {
          return false;
        }

        if (
          url.pathname === window.location.pathname &&
          url.search === window.location.search &&
          url.hash &&
          url.hash !== window.location.hash
        ) {
          return false;
        }
      } catch (error) {
        return false;
      }

      return true;
    }

    function shouldHandleForm(form, event) {
      if (!form || event.defaultPrevented) {
        return false;
      }

      if (form.hasAttribute('data-no-loader')) {
        return false;
      }

      var target = form.getAttribute('target');
      if (target && target !== '_self') {
        return false;
      }

      return true;
    }

    function afterLoaderPaint(callback) {
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(callback);
      });
    }

    function startTransition(callback) {
      if (transitionLocked) {
        return;
      }

      transitionLocked = true;
      showLoader();
      afterLoaderPaint(callback);
    }

    function shouldHandleProgrammaticFormSubmission(form) {
      if (!form) {
        return false;
      }

      if (form.hasAttribute('data-no-loader')) {
        return false;
      }

      var target = form.getAttribute('target');
      if (target && target !== '_self') {
        return false;
      }

      return true;
    }

    window.TicketlyGlobalLoader = {
      show: showLoader,
      hide: function () {
        transitionLocked = false;
        hideLoader(true);
      },
    };

    if (window.HTMLFormElement && window.HTMLFormElement.prototype) {
      nativeSubmit = window.HTMLFormElement.prototype.submit;

      if (typeof nativeSubmit === 'function') {
        window.HTMLFormElement.prototype.submit = function () {
          if (shouldHandleProgrammaticFormSubmission(this)) {
            var form = this;

            startTransition(function () {
              nativeSubmit.call(form);
            });

            return;
          }

          return nativeSubmit.call(this);
        };
      }
    }

    document.addEventListener('click', function (event) {
      var link = event.target.closest('a[href]');
      if (!shouldHandleLink(link, event)) {
        return;
      }

      event.preventDefault();

      startTransition(function () {
        window.location.assign(link.href);
      });
    });

    document.addEventListener('submit', function (event) {
      var form = event.target.closest('form');
      if (!shouldHandleForm(form, event)) {
        return;
      }

      if (form.dataset.ticketlyLoaderSubmitting === '1') {
        delete form.dataset.ticketlyLoaderSubmitting;
        return;
      }

      event.preventDefault();

      startTransition(function () {
        if (typeof nativeSubmit === 'function') {
          form.dataset.ticketlyLoaderSubmitting = '1';
          nativeSubmit.call(form);
          return;
        }

        form.submit();
      });
    });

    window.addEventListener('load', function () {
      transitionLocked = false;
      hideLoader(false);
    });

    window.addEventListener('pageshow', function () {
      transitionLocked = false;
      hideLoader(true);
    });

    window.addEventListener('error', function () {
      transitionLocked = false;
      hideLoader(true);
    });

    window.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        hideLoader(true);
      }
    });

    if (document.readyState === 'complete') {
      hideLoader(false);
    }
  })();
</script>
