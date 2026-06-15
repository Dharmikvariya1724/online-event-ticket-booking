<script>
  (function () {
    var showIcon = [
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">',
      '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>',
      '<circle cx="12" cy="12" r="3" stroke-width="1.8"/>',
      '</svg>'
    ].join('');

    var hideIcon = [
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">',
      '<path d="m3 3 18 18" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>',
      '<path d="M10.6 10.7a2.2 2.2 0 0 0 2.7 2.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>',
      '<path d="M9.9 5.2A11.4 11.4 0 0 1 12 5c6.5 0 10 7 10 7a18.3 18.3 0 0 1-3.2 4.1" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>',
      '<path d="M6.6 6.7A18.1 18.1 0 0 0 2 12s3.5 7 10 7a10.7 10.7 0 0 0 4.2-.8" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>',
      '</svg>'
    ].join('');

    function updateToggleState(input, button, isVisible) {
      var selectionStart = input.selectionStart;
      var selectionEnd = input.selectionEnd;

      input.type = isVisible ? 'text' : 'password';
      input.setAttribute('data-password-toggle-visible', isVisible ? 'true' : 'false');
      button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
      button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
      button.innerHTML = isVisible ? hideIcon : showIcon;

      if (typeof selectionStart === 'number' && typeof selectionEnd === 'number') {
        input.setSelectionRange(selectionStart, selectionEnd);
      }
    }

    function enhancePasswordField(input) {
      if (!(input instanceof HTMLInputElement)) return;
      if (input.dataset.passwordToggleBound === 'true') return;
      if (input.type !== 'password') return;

      var wrapper = document.createElement('div');
      wrapper.className = 'password-toggle-field';

      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
      input.dataset.passwordToggleBound = 'true';

      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'password-toggle-button';
      button.setAttribute('tabindex', '0');

      wrapper.appendChild(button);
      updateToggleState(input, button, false);

      button.addEventListener('click', function () {
        updateToggleState(input, button, input.type === 'password');
        input.focus({ preventScroll: true });
      });
    }

    function initPasswordToggles(root) {
      var scope = root instanceof Element || root instanceof Document ? root : document;
      scope.querySelectorAll('input[type="password"]').forEach(enhancePasswordField);
    }

    document.addEventListener('DOMContentLoaded', function () {
      initPasswordToggles(document);

      if (!document.body || typeof MutationObserver === 'undefined') return;

      var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          mutation.addedNodes.forEach(function (node) {
            if (!(node instanceof Element)) return;
            if (node.matches('input[type="password"]')) {
              enhancePasswordField(node);
              return;
            }

            initPasswordToggles(node);
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });
    });
  })();
</script>
