@extends($scannerLayout)

@php
    $pageTitle = $canValidate ? 'QR Ticket Scanner' : 'QR Ticket Viewer';
    $pageSubtitle = $canValidate
        ? ($scannerRole === 'admin' ? 'Admin validation enabled across events' : 'Validate tickets for your events only')
        : 'View event details from a ticket QR without validation access';
    $roleBadge = match ($scannerRole) {
        'admin' => 'Admin Mode',
        'organiser' => 'Organiser Mode',
        default => 'Viewer Mode',
    };
    $roleCopy = match ($scannerRole) {
        'admin' => 'You can validate tickets across events and see live verification results.',
        'organiser' => 'You can validate only tickets for events you organise.',
        default => 'Ticket validation is disabled for this session. Scanning a QR will open the event page when available.',
    };
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-subtitle', $pageSubtitle)

@section('page-icon')
<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600/70 text-indigo-200">
  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M18 3h2a1 1 0 011 1v2M18 21h2a1 1 0 001-1v-2M8 12h8M8 9h8M8 15h5"/>
  </svg>
</div>
@endsection

@section('scripts')
<script>
  function loadScannerLibrary() {
    return new Promise((resolve, reject) => {
      if (window.Html5Qrcode) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js';
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }
</script>
<script>
  const canValidate = @json($canValidate);
  const apiUrl = @json(route('api.scan.ticket', [], false));
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const handheldDevice = (() => {
    const ua = navigator.userAgent || '';
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua)
      || window.matchMedia('(pointer: coarse)').matches
      || window.innerWidth < 1024;
  })();

  const openScannerButton = document.getElementById('open-scanner');
  const stopScannerButton = document.getElementById('stop-scanner');
  const closeScannerButton = document.getElementById('close-scanner');
  const switchCameraButton = document.getElementById('switch-camera');
  const cameraSelectWrap = document.getElementById('camera-select-wrap');
  const cameraSelect = document.getElementById('camera-select');
  const cameraStatus = document.getElementById('camera-status');
  const scannerHelper = document.getElementById('scanner-helper');
  const scannerModal = document.getElementById('scanner-modal');
  const manualForm = document.getElementById('manual-form');
  const manualCodeInput = document.getElementById('manual-code');
  const manualSubmitButton = document.getElementById('manual-submit');
  const resultOverlay = document.getElementById('result-overlay');
  const resultCard = document.getElementById('result-card');
  const resultIcon = document.getElementById('result-icon');
  const resultIconSvg = document.getElementById('result-icon-svg');
  const resultTitle = document.getElementById('result-title');
  const resultMessage = document.getElementById('result-message');
  const resultDetails = document.getElementById('result-details');
  const closeResultButton = document.getElementById('close-result');

  const SCAN_DEBOUNCE_MS = 2500;
  let html5QrCode = null;
  let scannerLibraryReady = false;
  let loadingScannerLibrary = false;
  let cameras = [];
  let activeCameraId = null;
  let isScanning = false;
  let isProcessingScan = false;
  let lastScannedValue = '';
  let lastScannedAt = 0;

  function setScannerHelper(message) {
    scannerHelper.textContent = message || '';
  }

  function setCameraStatus(message) {
    cameraStatus.textContent = message || '';
  }

  function openOverlay(overlay) {
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
  }

  function closeOverlay(overlay) {
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
  }

  function clearResultDetails() {
    resultDetails.innerHTML = '';
  }

  function renderResultDetail(label, value) {
    if (!value) return;

    const row = document.createElement('div');
    row.className = 'scan-result-detail';
    row.innerHTML = `
      <span class="scan-result-detail__label">${label}</span>
      <span class="scan-result-detail__value">${value}</span>
    `;
    resultDetails.appendChild(row);
  }

  function setResultAppearance(type) {
    resultCard.className = 'scan-result-card';
    resultIcon.className = 'scan-result-icon';

    if (type === 'green') {
      resultCard.classList.add('scan-result-card--green');
      resultIcon.classList.add('scan-result-icon--green');
      resultIconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 12.5l4.2 4.2L19 7"></path>';
      return;
    }

    if (type === 'orange') {
      resultCard.classList.add('scan-result-card--orange');
      resultIcon.classList.add('scan-result-icon--orange');
      resultIconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M8 8l8 8M16 8l-8 8"></path>';
      return;
    }

    resultCard.classList.add('scan-result-card--red');
    resultIcon.classList.add('scan-result-icon--red');
    resultIconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M8 8l8 8M16 8l-8 8"></path>';
  }

  function showResult(options) {
    const {
      type = 'red',
      title = 'Scan error',
      message = 'Unable to process this ticket.',
      details = {}
    } = options || {};

    setResultAppearance(type);
    resultTitle.textContent = title;
    resultMessage.textContent = message;
    clearResultDetails();
    Object.entries(details).forEach(([label, value]) => renderResultDetail(label, value));
    openOverlay(resultOverlay);
  }

  async function ensureScannerLibrary() {
    if (scannerLibraryReady) return true;
    if (loadingScannerLibrary) return false;

    loadingScannerLibrary = true;

    try {
      await loadScannerLibrary();
      scannerLibraryReady = true;
      return true;
    } catch (error) {
      setScannerHelper('Scanner library failed to load. Please refresh and try again.');
      return false;
    } finally {
      loadingScannerLibrary = false;
    }
  }

  function getPreferredCameraId(cameraList) {
    if (!Array.isArray(cameraList) || cameraList.length === 0) return null;

    const backCamera = cameraList.find((camera) => /(back|rear|environment|world)/i.test(camera.label || ''));
    return (backCamera || cameraList[0]).id;
  }

  function syncCameraControls() {
    const multipleCameras = cameras.length > 1;
    cameraSelectWrap.classList.toggle('hidden', handheldDevice || !multipleCameras);
    switchCameraButton.classList.toggle('hidden', !handheldDevice || !multipleCameras);
    switchCameraButton.disabled = !multipleCameras;
  }

  function fillCameraSelect() {
    cameraSelect.innerHTML = '<option value="">Select camera</option>';

    cameras.forEach((camera, index) => {
      const option = document.createElement('option');
      option.value = camera.id;
      option.textContent = camera.label || `Camera ${index + 1}`;
      option.selected = camera.id === activeCameraId;
      cameraSelect.appendChild(option);
    });
  }

  function extractEventUrl(rawValue) {
    const text = String(rawValue || '').trim();
    if (!text) return null;

    try {
      const url = new URL(text, window.location.origin);
      const isSameOrigin = url.origin === window.location.origin;
      const isEventPage = /^\/events\/[^/]+/i.test(url.pathname);
      const isTicketScanRoute = /^\/tickets\/scan$/i.test(url.pathname);

      if (isSameOrigin && (isEventPage || isTicketScanRoute)) {
        return url.toString();
      }
    } catch (error) {
      // Fall through to encoded payload parsing.
    }

    const payload = decodeQrPayload(text);
    if (payload && typeof payload.event_url === 'string' && payload.event_url.trim() !== '') {
      return payload.event_url.trim();
    }

    return null;
  }

  function decodeQrPayload(rawValue) {
    const text = String(rawValue || '').trim();
    if (!text) return null;

    const normalized = text.replace(/-/g, '+').replace(/_/g, '/');
    const padded = normalized + '='.repeat((4 - (normalized.length % 4)) % 4);

    try {
      const parsed = JSON.parse(atob(padded));
      return parsed && typeof parsed === 'object' ? parsed : null;
    } catch (error) {
      return null;
    }
  }

  function shouldIgnoreScan(rawValue) {
    const normalizedValue = String(rawValue || '').trim();
    const now = Date.now();

    if (!normalizedValue || isProcessingScan) return true;
    if (normalizedValue === lastScannedValue && (now - lastScannedAt) < SCAN_DEBOUNCE_MS) return true;

    lastScannedValue = normalizedValue;
    lastScannedAt = now;
    return false;
  }

  async function fetchCameras() {
    cameras = await Html5Qrcode.getCameras();
    activeCameraId = activeCameraId || getPreferredCameraId(cameras);
    fillCameraSelect();
    syncCameraControls();

    if (!cameras.length) {
      throw new Error('No camera detected on this device.');
    }
  }

  function currentScanConfig() {
    const frameSize = handheldDevice ? 240 : 300;

    return {
      fps: handheldDevice ? 12 : 10,
      qrbox: { width: frameSize, height: frameSize },
      aspectRatio: handheldDevice ? 1 : 1.333334,
    };
  }

  async function startScanner(cameraId = null) {
    const scannerReady = await ensureScannerLibrary();
    if (!scannerReady || typeof Html5Qrcode === 'undefined') return;

    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode('qr-reader');
    }

    if (!cameras.length) {
      await fetchCameras();
    }

    const selectedCameraId = cameraId || activeCameraId || getPreferredCameraId(cameras);
    activeCameraId = selectedCameraId;
    fillCameraSelect();

    const onScanSuccess = async (decodedText) => {
      if (shouldIgnoreScan(decodedText)) return;

      isProcessingScan = true;

      try {
        await stopScanner();
        closeOverlay(scannerModal);

        if (!canValidate) {
          const eventUrl = extractEventUrl(decodedText);

          if (eventUrl) {
            window.location.assign(eventUrl);
            return;
          }

          showResult({
            type: 'red',
            title: 'Event link unavailable',
            message: 'This QR code does not contain an event page link that can be opened in viewer mode.',
          });
          return;
        }

        await submitScan(decodedText);
      } finally {
        isProcessingScan = false;
      }
    };

    const tryBackCamera = handheldDevice && !cameraId;
    const scanConfig = currentScanConfig();
    let currentCameraLabel = 'camera';

    try {
      setCameraStatus('Requesting camera access...');

      if (tryBackCamera) {
        try {
          await html5QrCode.start(
            { facingMode: { exact: 'environment' } },
            scanConfig,
            onScanSuccess,
            () => {}
          );
          currentCameraLabel = 'back camera';
        } catch (error) {
          await html5QrCode.start(
            { deviceId: { exact: selectedCameraId } },
            scanConfig,
            onScanSuccess,
            () => {}
          );
          currentCameraLabel = cameras.find((camera) => camera.id === selectedCameraId)?.label || 'camera';
        }
      } else {
        await html5QrCode.start(
          { deviceId: { exact: selectedCameraId } },
          scanConfig,
          onScanSuccess,
          () => {}
        );
        currentCameraLabel = cameras.find((camera) => camera.id === selectedCameraId)?.label || 'camera';
      }

      isScanning = true;
      stopScannerButton.disabled = false;
      setCameraStatus(`Scanning with ${currentCameraLabel}. Hold the QR steady inside the frame.`);
      setScannerHelper(handheldDevice
        ? 'Mobile/tablet detected. Back camera is preferred by default.'
        : 'Desktop detected. You can choose a specific camera from the list.'
      );
    } catch (error) {
      isScanning = false;
      stopScannerButton.disabled = true;
      setCameraStatus(error?.message || 'Camera access failed. Check browser permissions and try again.');
    }
  }

  async function stopScanner() {
    if (!html5QrCode || !isScanning) {
      stopScannerButton.disabled = true;
      return;
    }

    try {
      await html5QrCode.stop();
      await html5QrCode.clear();
    } catch (error) {
      // Ignore html5-qrcode stop races.
    } finally {
      isScanning = false;
      stopScannerButton.disabled = true;
      setCameraStatus('Camera stopped.');
    }
  }

  async function openScanner() {
    openOverlay(scannerModal);
    setCameraStatus('Preparing scanner...');
    setScannerHelper('Opening camera access...');

    try {
      await startScanner();
    } catch (error) {
      setCameraStatus('Unable to start scanner. Please check camera permissions.');
    }
  }

  async function submitScan(rawValue) {
    const payload = { qr_code: String(rawValue || '').trim() };

    if (!payload.qr_code) {
      showResult({
        type: 'red',
        title: 'Missing ticket code',
        message: 'Please scan a QR code or enter a valid ticket UUID or booking reference.',
      });
      return;
    }

    try {
      const response = await fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));
      const type = data.type || 'red';
      const titleMap = {
        verified: 'Ticket Verified',
        already_used: 'Ticket Already Used',
        entry_not_started: 'Ticket Scanned Too Early',
        entry_closed: 'Ticket Expired',
        cancelled_or_refunded: 'Ticket Not Valid',
      };

      showResult({
        type,
        title: titleMap[data.code] || data.message || (type === 'green' ? 'Ticket Verified' : 'Ticket Invalid'),
        message: data.message || (type === 'green' ? 'Ticket Verified' : 'Ticket Invalid'),
        details: {
          'Event': data.event_title,
          'Ticket UUID': data.ticket_uuid,
          'Booking Ref': data.booking_reference,
          'Validate Starts': data.validation_starts_at ? new Date(data.validation_starts_at).toLocaleString() : '',
          'Validate Ends': data.validation_ends_at ? new Date(data.validation_ends_at).toLocaleString() : '',
          'Scanned At': data.scanned_at ? new Date(data.scanned_at).toLocaleString() : '',
        }
      });
    } catch (error) {
      showResult({
        type: 'red',
        title: 'Validation API unavailable',
        message: 'Unable to reach the scan API right now. Please try again in a moment.',
      });
    }
  }

  openScannerButton.addEventListener('click', openScanner);

  stopScannerButton.addEventListener('click', async () => {
    await stopScanner();
  });

  closeScannerButton.addEventListener('click', async () => {
    await stopScanner();
    closeOverlay(scannerModal);
  });

  switchCameraButton.addEventListener('click', async () => {
    if (cameras.length < 2) return;

    const currentIndex = cameras.findIndex((camera) => camera.id === activeCameraId);
    const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % cameras.length : 0;
    activeCameraId = cameras[nextIndex].id;

    await stopScanner();
    await startScanner(activeCameraId);
  });

  cameraSelect.addEventListener('change', async (event) => {
    const selectedCameraId = event.target.value;
    if (!selectedCameraId) return;

    activeCameraId = selectedCameraId;
    await stopScanner();
    await startScanner(activeCameraId);
  });

  manualForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!canValidate) return;
    await submitScan(manualCodeInput.value);
  });

  function syncManualButton() {
    if (!canValidate) {
      manualSubmitButton.disabled = true;
      return;
    }

    manualSubmitButton.disabled = manualCodeInput.value.trim() === '';
  }

  manualCodeInput.addEventListener('input', syncManualButton);
  syncManualButton();

  closeResultButton.addEventListener('click', () => closeOverlay(resultOverlay));

  scannerModal.addEventListener('click', async (event) => {
    if (event.target === scannerModal) {
      await stopScanner();
      closeOverlay(scannerModal);
    }
  });

  resultOverlay.addEventListener('click', (event) => {
    if (event.target === resultOverlay) {
      closeOverlay(resultOverlay);
    }
  });

  document.addEventListener('keydown', async (event) => {
    if (event.key !== 'Escape') return;

    if (!scannerModal.classList.contains('hidden')) {
      await stopScanner();
      closeOverlay(scannerModal);
    }

    if (!resultOverlay.classList.contains('hidden')) {
      closeOverlay(resultOverlay);
    }
  });
</script>
@endsection

@section('content')
<div class="{{ $scannerLayout === 'layouts.app' ? 'mx-auto max-w-7xl px-4 py-8 sm:py-12' : 'mx-auto max-w-7xl' }}">
  <div class="scanner-shell">
    <section class="scanner-card scanner-hero p-5 sm:p-8">
      <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-3xl">
          <span class="scanner-status-chip {{ $canValidate ? 'scanner-status-chip--green' : 'scanner-status-chip--amber' }}">
            <span class="scanner-status-chip__dot"></span>
            {{ $roleBadge }}
          </span>
          <h1 class="mt-5 text-3xl font-black tracking-[-0.04em] text-slate-950 sm:text-5xl">{{ $pageTitle }}</h1>
          <p class="mt-4 max-w-2xl text-[1.02rem] leading-8 text-slate-600">{{ $roleCopy }}</p>
        </div>
        <div class="scanner-note {{ $canValidate ? 'scanner-note--validator' : 'scanner-note--viewer' }} max-w-sm">
          <p class="text-sm font-semibold text-slate-900">{{ $canValidate ? 'Validation access enabled' : 'Validation access disabled' }}</p>
          <p class="mt-2 text-sm leading-6 text-slate-600">
            {{ $canValidate
                ? 'The API will verify event ownership, validation time, refund state, and duplicate use before marking a ticket as used.'
                : 'You can still scan a ticket QR, but it will only open the event details page when the QR contains a valid event URL.' }}
          </p>
        </div>
      </div>

      <div class="mt-8 scanner-preview">
        <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 px-6 text-center">
          <div class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-white shadow-[0_24px_50px_rgba(99,102,241,0.28)]">
            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M18 3h2a1 1 0 011 1v2M18 21h2a1 1 0 001-1v-2M9 9h6v6H9z"/>
            </svg>
          </div>
          <div>
            <p class="text-xl font-bold text-slate-950">{{ $canValidate ? 'Open camera and verify tickets' : 'Open camera and view event tickets' }}</p>
            <p class="mt-2 text-sm text-slate-500">Position the QR inside the frame. The scanner will debounce duplicate reads automatically.</p>
          </div>
        </div>
        <div class="scanner-preview__frame"></div>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <button id="open-scanner" type="button" class="scanner-control scanner-control--primary">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            <circle cx="12" cy="13" r="3" stroke-width="2"/>
          </svg>
          <span>{{ $canValidate ? 'Start Scanning' : 'Scan Event QR' }}</span>
        </button>
        <button id="stop-scanner" type="button" class="scanner-control scanner-control--danger" disabled>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <rect x="7" y="7" width="10" height="10" rx="2"></rect>
          </svg>
          <span>Stop Camera</span>
        </button>
      </div>

      <p id="scanner-helper" class="mt-4 text-sm text-slate-500">Camera access starts only after you press the scan button.</p>
    </section>

    <aside class="space-y-6">
      <section class="scanner-card p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-slate-950">Manual Validation</h2>
          <span class="rounded-full px-3 py-1 text-xs font-bold {{ $canValidate ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
            {{ $canValidate ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          {{ $canValidate
              ? 'Paste a ticket UUID, booking reference, or the full QR payload URL.'
              : 'Manual ticket validation is available only for authenticated organisers or admins.' }}
        </p>

        <form id="manual-form" class="mt-5 space-y-3">
          <label class="block text-xs font-bold uppercase tracking-[0.14em] text-slate-500" for="manual-code">Ticket UUID or QR payload</label>
          <input id="manual-code" type="text" class="scanner-input" placeholder="Paste ticket UUID, TKT- reference, or full QR URL" @disabled(!$canValidate)>
          <button id="manual-submit" type="submit" class="scanner-control scanner-control--primary w-full justify-center" @disabled(!$canValidate)>
            Validate Ticket
          </button>
        </form>
      </section>

      <section class="scanner-card p-5 sm:p-6">
        <h2 class="text-lg font-bold text-slate-950">Validation Rules</h2>
        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
          <li class="scanner-note">Before validation start: red result with Ticket Scanned Too Early.</li>
          <li class="scanner-note">After validation end: red result with Ticket Expired.</li>
          <li class="scanner-note">Wrong organiser ticket: red result with Invalid Organizer Ticket.</li>
          <li class="scanner-note">Cancelled or refunded ticket: red result with Ticket Not Valid.</li>
          <li class="scanner-note">Already used ticket: orange result with Ticket Already Used.</li>
          <li class="scanner-note">First valid scan: green result with Ticket Verified.</li>
        </ul>
      </section>
    </aside>
  </div>
</div>

<div id="scanner-modal" class="scanner-modal fixed inset-0 z-50 hidden">
  <div class="scanner-modal__panel">
    <div class="scanner-modal__header">
      <div>
        <h3 class="text-lg font-bold">Live Camera Scanner</h3>
        <p id="camera-status" class="mt-1 text-sm text-slate-400">Preparing camera access.</p>
      </div>
      <button id="close-scanner" type="button" class="scanner-close" aria-label="Close scanner">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"></path>
        </svg>
      </button>
    </div>

    <div class="scanner-modal__body">
      <div id="qr-reader"></div>
    </div>

    <div class="scanner-modal__footer">
      <div id="camera-select-wrap" class="hidden min-w-[16rem] flex-1">
        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400" for="camera-select">Desktop Camera</label>
        <select id="camera-select" class="scanner-select bg-slate-950 text-slate-100 border-slate-700">
          <option value="">Select camera</option>
        </select>
      </div>

      <button id="switch-camera" type="button" class="scanner-control scanner-control--secondary hidden">
        Switch Camera
      </button>
    </div>
  </div>
</div>

<div id="result-overlay" class="scan-result-overlay fixed inset-0 z-[60] hidden items-center justify-center px-4">
  <div id="result-card" class="scan-result-card scan-result-card--green">
    <div class="scan-result-top">
      <button id="close-result" type="button" class="scan-result-close" aria-label="Close result">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"></path>
        </svg>
      </button>
    </div>

    <div class="scan-result-body">
      <div id="result-icon" class="scan-result-icon scan-result-icon--green">
        <svg id="result-icon-svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 12.5l4.2 4.2L19 7"></path>
        </svg>
      </div>
      <h3 id="result-title" class="text-2xl font-black tracking-[-0.03em] text-slate-950">Ticket Verified</h3>
      <p id="result-message" class="scan-result-message mt-4">Ticket Verified</p>
      <div id="result-details" class="scan-result-details"></div>
    </div>
  </div>
</div>
@endsection

@section('head')
<style>
  .scanner-shell {
    display: grid;
    gap: 1.5rem;
  }

  @media (min-width: 1200px) {
    .scanner-shell {
      grid-template-columns: minmax(0, 1fr) 24rem;
      align-items: start;
    }
  }

  .scanner-card {
    border: 1px solid #e2e8f0;
    border-radius: 1.75rem;
    background: #ffffff;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
  }

  .scanner-hero {
    background:
      radial-gradient(circle at top left, rgba(99, 102, 241, 0.18), transparent 34%),
      radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.12), transparent 30%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }

  .scanner-preview {
    position: relative;
    min-height: 21rem;
    overflow: hidden;
    border-radius: 1.5rem;
    border: 1px solid #e2e8f0;
    background:
      linear-gradient(135deg, rgba(15, 23, 42, 0.04), rgba(99, 102, 241, 0.05)),
      #f8fafc;
  }

  .scanner-preview::before {
    content: "";
    position: absolute;
    inset: 1.25rem;
    border-radius: 1.25rem;
    border: 2px dashed rgba(99, 102, 241, 0.28);
    pointer-events: none;
  }

  .scanner-preview__frame {
    position: absolute;
    inset: 50% auto auto 50%;
    width: min(18rem, calc(100% - 5rem));
    aspect-ratio: 1 / 1;
    transform: translate(-50%, -50%);
    border-radius: 1.5rem;
    border: 3px solid rgba(99, 102, 241, 0.55);
    box-shadow:
      0 0 0 9999px rgba(15, 23, 42, 0.36),
      0 0 0 1px rgba(255, 255, 255, 0.2) inset;
    pointer-events: none;
  }

  .scanner-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    padding: 0.55rem 0.85rem;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .scanner-status-chip__dot {
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 999px;
    background: currentColor;
  }

  .scanner-status-chip--green {
    border-color: #a7f3d0;
    background: #ecfdf5;
    color: #047857;
  }

  .scanner-status-chip--amber {
    border-color: #fde68a;
    background: #fffbeb;
    color: #b45309;
  }

  .scanner-control {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    border-radius: 1rem;
    border: 1px solid #cbd5e1;
    padding: 0.95rem 1.15rem;
    font-size: 0.95rem;
    font-weight: 700;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }

  .scanner-control:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
  }

  .scanner-control:disabled {
    cursor: not-allowed;
    opacity: 0.58;
  }

  .scanner-control--primary {
    border-color: transparent;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff;
  }

  .scanner-control--secondary {
    background: #ffffff;
    color: #0f172a;
  }

  .scanner-control--danger {
    border-color: transparent;
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: #ffffff;
  }

  .scanner-note {
    border-radius: 1.1rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 1rem 1.1rem;
  }

  .scanner-note--viewer {
    border-color: #fde68a;
    background: #fffbeb;
  }

  .scanner-note--validator {
    border-color: #bfdbfe;
    background: #eff6ff;
  }

  .scanner-input,
  .scanner-select {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    padding: 0.95rem 1rem;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .scanner-input:focus,
  .scanner-select:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
  }

  .scanner-modal {
    background: rgba(2, 6, 23, 0.88);
    backdrop-filter: blur(14px);
  }

  .scanner-modal__panel {
    display: flex;
    flex-direction: column;
    width: min(100%, 72rem);
    height: min(100%, 92vh);
    margin: auto;
    border-radius: 1.75rem;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.26);
    background: #020617;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.42);
  }

  .scanner-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.15rem 1.25rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    color: #e2e8f0;
  }

  .scanner-modal__body {
    position: relative;
    flex: 1;
    min-height: 24rem;
    background: #020617;
  }

  .scanner-modal__footer {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    padding: 1rem 1.25rem 1.25rem;
    border-top: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(15, 23, 42, 0.88);
  }

  .scanner-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: rgba(15, 23, 42, 0.66);
    color: #e2e8f0;
  }

  .scanner-close:hover {
    background: rgba(30, 41, 59, 0.95);
  }

  #qr-reader {
    height: 100%;
    width: 100%;
  }

  #qr-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
  }

  #qr-reader__scan_region {
    min-height: 100%;
  }

  .scan-result-overlay {
    background: rgba(2, 6, 23, 0.8);
    backdrop-filter: blur(12px);
  }

  .scan-result-card {
    width: min(100%, 38rem);
    border-radius: 2rem;
    border: 1px solid transparent;
    background: #ffffff;
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
    overflow: hidden;
  }

  .scan-result-card--green {
    border-color: #a7f3d0;
  }

  .scan-result-card--orange {
    border-color: #fdba74;
  }

  .scan-result-card--red {
    border-color: #fca5a5;
  }

  .scan-result-top {
    padding: 1.25rem 1.25rem 0;
    display: flex;
    justify-content: flex-end;
  }

  .scan-result-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
  }

  .scan-result-body {
    padding: 0 2rem 2rem;
    text-align: center;
  }

  .scan-result-icon {
    width: 7rem;
    height: 7rem;
    margin: 0 auto 1.5rem;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
  }

  .scan-result-icon--green {
    background: linear-gradient(135deg, #10b981, #059669);
  }

  .scan-result-icon--orange {
    background: linear-gradient(135deg, #f59e0b, #ea580c);
  }

  .scan-result-icon--red {
    background: linear-gradient(135deg, #ef4444, #dc2626);
  }

  .scan-result-message {
    font-size: 1.18rem;
    line-height: 1.75rem;
    color: #0f172a;
  }

  .scan-result-details {
    margin-top: 1.5rem;
    display: grid;
    gap: 0.75rem;
    text-align: left;
  }

  .scan-result-detail {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 0.85rem 1rem;
    font-size: 0.9rem;
  }

  .scan-result-detail__label {
    color: #64748b;
    font-weight: 700;
  }

  .scan-result-detail__value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
  }

  @media (max-width: 767px) {
    .scan-result-body {
      padding: 0 1.25rem 1.5rem;
    }

    .scan-result-icon {
      width: 6rem;
      height: 6rem;
    }
  }
</style>
@endsection
