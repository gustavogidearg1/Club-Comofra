{{-- resources/views/redeems/manual-index.blade.php --}}
@extends('layouts.app')
@section('title','Consumo manual')

@section('content')
<style>
  .saldo-chip{
    display:inline-flex; align-items:center; gap:.5rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }

  /* stats (opcional) */
  .summary-card{
    border-radius:10px; border:none;
    box-shadow:0 0.125rem 0.25rem rgba(0,0,0,.075);
    transition:transform .2s;
  }
  .summary-card:hover{ transform:translateY(-2px); }
  .summary-icon{ font-size:2rem; opacity:.8; }
  .stats-number{ font-size:1.6rem; font-weight:bold; margin-bottom:.25rem; }
  .stats-label{ font-size:.9rem; color:#6c757d; }
</style>

<div class="container py-3">
  <div class="card mat-card">

    <div class="mat-header flex-wrap">
      <h3 class="mat-title mb-0 flex-grow-1" style="min-width: 220px;">
        <i class="bi bi-shop me-2"></i> Consumo manual (Empleado → Negocio)
      </h3>

      <div class="d-flex flex-wrap align-items-center gap-2 ms-auto mt-2 mt-lg-0" style="max-width:100%;">
        <span class="saldo-chip" title="Saldo disponible">
          <i class="bi bi-wallet2"></i>
          <span class="d-none d-sm-inline">Saldo:</span>
          <strong>{{ number_format($saldo) }}</strong>
        </span>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i>
          <span class="d-none d-sm-inline">Volver</span>
        </a>
      </div>
    </div>

    <div class="card-body p-4">

      {{-- ============================
         Estadísticas rápidas (SI existe $stats)
         (si no las pasás, este bloque no aparece)
         ============================ --}}
      @if(!empty($stats))
        <div class="row g-2 mb-4">
          <div class="col-6 col-md-3">
            <div class="card summary-card h-100">
              <div class="card-body text-center">
                <div class="text-primary"><i class="bi bi-shop summary-icon"></i></div>
                <div class="stats-number">{{ number_format($stats['businesses'] ?? 0) }}</div>
                <div class="stats-label">Negocios</div>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card summary-card h-100">
              <div class="card-body text-center">
                <div class="text-success"><i class="bi bi-arrow-down-circle summary-icon"></i></div>
                <div class="stats-number">{{ number_format($stats['redeems_count'] ?? 0) }}</div>
                <div class="stats-label">Consumos</div>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card summary-card h-100">
              <div class="card-body text-center">
                <div class="text-danger"><i class="bi bi-wallet2 summary-icon"></i></div>
                <div class="stats-number">{{ number_format($stats['redeemed_points'] ?? 0) }}</div>
                <div class="stats-label">Puntos consumidos</div>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="card summary-card h-100">
              <div class="card-body text-center">
                <div class="text-info"><i class="bi bi-graph-up summary-icon"></i></div>
                <div class="stats-number">{{ number_format($stats['avg_points'] ?? 0, 1) }}</div>
                <div class="stats-label">Promedio</div>
              </div>
            </div>
          </div>
        </div>
      @endif

      {{-- ============================
         Selector + Buscar + Scanner (misma línea)
         ============================ --}}
      <div class="mb-2">
        <label class="form-label fw-semibold">Elegí el negocio</label>

        <div class="input-group">
          <select id="businessSelect" class="form-select" required>
            <option value="">Seleccionar negocio…</option>
            @foreach($businesses as $b)
              <option value="{{ $b->id }}" data-name="{{ $b->name }}">
                {{ $b->name }}
              </option>
            @endforeach
          </select>

          {{-- Buscar (modal) --}}
          <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-toggle="modal"
                  data-bs-target="#modalBusinessSearch"
                  title="Buscar negocio">
            <i class="bi bi-search"></i>
          </button>

          {{-- Scanner QR --}}
          <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-toggle="modal"
                  data-bs-target="#qrScanModal"
                  title="Escanear QR">
            <i class="bi bi-qr-code-scan"></i>
          </button>
        </div>

        <div class="form-text text-muted">
          Podés elegir del combo, buscar por nombre (lupa) o escanear QR.
        </div>
      </div>

      {{-- CTA --}}
      <div class="d-grid d-md-flex gap-2 mt-3">
        <a id="btnContinue"
           href="#"
           class="btn btn-primary btn-mat w-100 w-md-auto disabled"
           aria-disabled="true">
          <i class="bi bi-arrow-right-circle"></i> Continuar
        </a>
      </div>

    </div>
  </div>
</div>

{{-- ============================
   MODAL: Buscar negocio (sin AJAX)
   ============================ --}}
<div class="modal fade" id="modalBusinessSearch" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-search me-1"></i> Buscar negocio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2 align-items-end mb-2">
          <div class="col-12 col-md-8">
            <label class="form-label">Buscar</label>
            <input type="text" id="bizSearchInput" class="form-control"
                   placeholder="Escribí el nombre del negocio">
          </div>

          <div class="col-12 col-md-4 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary w-100" id="bizClearBtn">
              <i class="bi bi-x-circle"></i> Limpiar
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Negocio</th>
                <th class="text-end" style="width:140px;">Acción</th>
              </tr>
            </thead>
            <tbody id="bizResultsBody"></tbody>
          </table>
        </div>

        <div class="text-muted small mt-2" id="bizResultsInfo"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-mat" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ============================
   MODAL: Scanner QR (html5-qrcode)
   ============================ --}}
<div class="modal fade" id="qrScanModal" tabindex="-1" aria-labelledby="qrScanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content mat-card">
      <div class="modal-header mat-header">
        <h5 class="modal-title mat-title" id="qrScanModalLabel">
          <i class="bi bi-camera"></i> Escanear QR del negocio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-info">
          Apuntá la cámara al QR del negocio. Te vamos a llevar al consumo con el negocio precargado.
        </div>

        <div class="border rounded-4 p-2 bg-dark">
          <div id="qrReader" style="width:100%;"></div>
        </div>

        <div id="qrScanError" class="text-danger small mt-2" style="display:none;"></div>
        <div class="text-muted small mt-2">
          Si no te pide permisos, revisá permisos de cámara del navegador.
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // ===========================
  // Continuar según select
  // ===========================
  const businessSelect = document.getElementById('businessSelect');
  const btnContinue    = document.getElementById('btnContinue');

  // armamos template de URL (sin depender del controlador)
  const urlTemplate = @json(route('redeems.manual.create', ['business' => '__ID__']));

  function updateContinue(){
    const id = businessSelect?.value || '';
    if (!btnContinue) return;

    if (!id) {
      btnContinue.href = '#';
      btnContinue.classList.add('disabled');
      btnContinue.setAttribute('aria-disabled','true');
      return;
    }

    btnContinue.href = urlTemplate.replace('__ID__', id);
    btnContinue.classList.remove('disabled');
    btnContinue.removeAttribute('aria-disabled');
  }

  businessSelect?.addEventListener('change', updateContinue);
  updateContinue();

  // =========================
  // MODAL BUSCAR NEGOCIO (sin AJAX)
  // =========================
  const bizModalEl  = document.getElementById('modalBusinessSearch');
  const searchInput = document.getElementById('bizSearchInput');
  const clearBtn    = document.getElementById('bizClearBtn');
  const bodyEl      = document.getElementById('bizResultsBody');
  const infoEl      = document.getElementById('bizResultsInfo');

  const hasBizModal =
    businessSelect && bizModalEl && searchInput && clearBtn && bodyEl && infoEl;

  let businesses = [];

  function normalize(s){
    return (s || '').toString().toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function escapeHtml(str){
    return (str || '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function renderBusinesses(){
    if (!hasBizModal) return;

    const q = normalize(searchInput.value);

    const filtered = businesses.filter(b => {
      const hay = normalize(b.name);
      return !q || hay.includes(q);
    });

    bodyEl.innerHTML = '';

    filtered.slice(0, 200).forEach(b => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><div class="fw-semibold">${escapeHtml(b.name)}</div></td>
        <td class="text-end">
          <button type="button" class="btn btn-primary btn-sm" data-biz-id="${b.id}">
            Seleccionar
          </button>
        </td>
      `;
      bodyEl.appendChild(tr);
    });

    const shown = Math.min(filtered.length, 200);
    infoEl.textContent = filtered.length
      ? `Mostrando ${shown} de ${filtered.length} resultados.`
      : 'No se encontraron resultados.';
  }

  if (hasBizModal) {
    businesses = Array.from(businessSelect.querySelectorAll('option'))
      .filter(o => o.value)
      .map(o => ({
        id: o.value,
        name: (o.dataset.name || o.textContent || '').trim()
      }));

    bodyEl.addEventListener('click', (ev) => {
      const btnSel = ev.target.closest('button[data-biz-id]');
      if(!btnSel) return;

      const id = btnSel.getAttribute('data-biz-id');
      businessSelect.value = id;
      businessSelect.dispatchEvent(new Event('change', {bubbles:true}));

      const closeBtn = bizModalEl.querySelector('[data-bs-dismiss="modal"]');
      if (closeBtn) closeBtn.click();
    });

    searchInput.addEventListener('input', renderBusinesses);

    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      renderBusinesses();
      searchInput.focus();
    });

    bizModalEl.addEventListener('shown.bs.modal', () => {
      renderBusinesses();
      searchInput.focus();
      searchInput.select();
    });
  }

  // ===========================
  // Scanner QR (html5-qrcode)
  // ===========================
  const qrModalEl = document.getElementById('qrScanModal');
  const errEl     = document.getElementById('qrScanError');
  const readerId  = "qrReader";

  if (!qrModalEl) return;

  let qr = null;
  let isRunning = false;

  function showErr(msg){
    if (!errEl) return;
    errEl.textContent = msg || '';
    errEl.style.display = msg ? '' : 'none';
  }

  function extractBusinessId(text){
    try {
      const url = new URL(text, window.location.origin);
      const parts = url.pathname.split('/').filter(Boolean);
      const i = parts.findIndex(p => p === 'manual');
      if (i >= 0 && parts[i+1]) return parts[i+1];
    } catch (e) {
      const parts = String(text).split('/').filter(Boolean);
      const i = parts.findIndex(p => p === 'manual');
      if (i >= 0 && parts[i+1]) return parts[i+1];
    }
    return null;
  }

  async function startQr(){
    showErr('');

    if (isRunning) return;
    isRunning = true;

    const el = document.getElementById(readerId);
    if (el) el.innerHTML = '';

    try {
      qr = new Html5Qrcode(readerId);

      await qr.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        (decodedText) => {
          const businessId = extractBusinessId(decodedText);
          if (!businessId) return;

          stopQr().finally(() => {
            window.location.href = `/redeems/manual/${businessId}`;
          });
        },
        () => {}
      );
    } catch (e) {
      isRunning = false;
      showErr('No se pudo abrir la cámara. Revisá permisos del navegador.');
    }
  }

  async function stopQr(){
    if (!qr) { isRunning = false; return; }

    try { await qr.stop(); } catch(e) {}
    try { await qr.clear(); } catch(e) {}
    qr = null;
    isRunning = false;

    const el = document.getElementById(readerId);
    if (el) el.innerHTML = '';
  }

  qrModalEl.addEventListener('shown.bs.modal', startQr);
  qrModalEl.addEventListener('hidden.bs.modal', stopQr);
});
</script>
@endpush
@endsection
