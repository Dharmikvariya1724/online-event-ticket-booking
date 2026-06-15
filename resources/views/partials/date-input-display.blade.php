<script>
  (function () {
    var DATE_SELECTOR = 'input[type="date"], input[type="datetime-local"]';
    var dateFormatter = new Intl.DateTimeFormat('en-US', {
      weekday: 'long',
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
    var timeFormatter = new Intl.DateTimeFormat('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });

    function normalizeTime(value) {
      return value.replace(/\s+/g, '').toLowerCase();
    }

    function getDefaultPlaceholder(originalType) {
      return originalType === 'date' ? 'Select date' : 'Select date and time';
    }

    function parseValue(rawValue, originalType) {
      if (!rawValue) return null;

      if (originalType === 'date') {
        var dateParts = rawValue.split('-').map(Number);
        if (dateParts.length !== 3 || dateParts.some(Number.isNaN)) return null;
        return new Date(dateParts[0], dateParts[1] - 1, dateParts[2], 0, 0, 0, 0);
      }

      if (originalType === 'datetime-local') {
        var parts = rawValue.split('T');
        if (parts.length !== 2) return null;

        var datePart = parts[0].split('-').map(Number);
        var timePart = parts[1].split(':').map(Number);

        if (datePart.length !== 3 || timePart.length < 2) return null;
        if (datePart.some(Number.isNaN) || timePart.slice(0, 2).some(Number.isNaN)) return null;

        return new Date(datePart[0], datePart[1] - 1, datePart[2], timePart[0], timePart[1], 0, 0);
      }

      return null;
    }

    function formatValue(rawValue, originalType) {
      var parsed = parseValue(rawValue, originalType);
      if (!parsed) return rawValue || '';

      if (originalType === 'date') {
        return dateFormatter.format(parsed);
      }

      return dateFormatter.format(parsed) + ' ' + normalizeTime(timeFormatter.format(parsed));
    }

    function switchToText(input) {
      var rawValue = input.dataset.rawValue || '';
      input.type = 'text';
      input.readOnly = true;
      input.value = rawValue ? formatValue(rawValue, input.dataset.originalType) : '';
      input.placeholder = rawValue ? '' : (input.dataset.datePlaceholder || getDefaultPlaceholder(input.dataset.originalType));
    }

    function switchToNative(input, openPicker) {
      input.type = input.dataset.originalType;
      input.readOnly = false;
      input.value = input.dataset.rawValue || '';
      input.placeholder = '';

      if (openPicker && typeof input.showPicker === 'function') {
        try {
          input.showPicker();
        } catch (error) {
        }
      }
    }

    function syncRawValue(input) {
      if (input.type === input.dataset.originalType) {
        input.dataset.rawValue = input.value || '';
      }
    }

    function initializeInput(input) {
      if (input.dataset.dateDisplayInitialized === 'true' || input.classList.contains('js-datetime-input')) {
        return;
      }

      input.dataset.dateDisplayInitialized = 'true';
      input.dataset.originalType = input.type;
      input.dataset.rawValue = input.value || '';
      input.dataset.datePlaceholder = input.getAttribute('placeholder') || getDefaultPlaceholder(input.type);

      input.addEventListener('focus', function () {
        if (input.type === 'text') {
          switchToNative(input, false);
        }
      });

      input.addEventListener('click', function () {
        if (input.type === 'text') {
          switchToNative(input, true);
          return;
        }

        if (typeof input.showPicker === 'function') {
          try {
            input.showPicker();
          } catch (error) {
          }
        }
      });

      input.addEventListener('change', function () {
        syncRawValue(input);
      });

      input.addEventListener('blur', function () {
        syncRawValue(input);
        switchToText(input);
      });

      switchToText(input);
    }

    document.querySelectorAll(DATE_SELECTOR).forEach(initializeInput);

    document.addEventListener('submit', function (event) {
      var form = event.target;
      if (!form || typeof form.querySelectorAll !== 'function') return;

      form.querySelectorAll('[data-date-display-initialized="true"]').forEach(function (input) {
        input.type = input.dataset.originalType;
        input.readOnly = false;
        input.value = input.dataset.rawValue || '';
      });
    }, true);
  })();
</script>
