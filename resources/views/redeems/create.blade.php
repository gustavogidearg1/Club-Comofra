@extends('layouts.app')
@section('title','Crear consumo')

@section('content')
<style>
  .input-group.flex-nowrap .form-select { min-width: 0; }
  .input-group.flex-nowrap .btn { white-space: nowrap; }
  .list-group-item-action { cursor: pointer; }
  .muted-hint { color:#6c757d; font-size:.9rem; }

  .chip{
    display:inline-flex; align-items:center; gap:.45rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }
</style>

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header">
      <h3 class="mat-title mb-0">
        <i class="bi bi-qr-code-scan me-2"></i> Crear consumo (Negocio)
      </h3>
      <div class="ms-auto">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
      </div>
    </div>

    <div class="card-body p-4">
      @if (session('error'))
        <div class="alert alert-danger mat-alert">{{ session('error') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger mat-alert">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Negocio logueado --}}
      <div class="mb-3 d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Negocio</div>
          <div class="fw-semibold">
            <i class="bi bi-shop me-1"></i> {{ auth()->user()->name }}
          </div>
        </div>

      </div>

      <form method="POST" action="{{ route('redeems.store') }}" id="redeemForm">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Empleado</label>

            <div class="input-group flex-nowrap">
              <select id="employeeSelect"
                      name="employee_user_id"
                      class="form-select @error('employee_user_id') is-invalid @enderror"
                      required>
                <option value="">Seleccionar...</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}"
                          data-name="{{ $e->name }}"
                          data-cuil="{{ $e->cuil ?? '' }}"
                          data-email="{{ $e->email ?? '' }}"
                          {{ old('employee_user_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }} {{ $e->cuil ? '— '.$e->cuil : '' }}
                  </option>
                @endforeach
              </select>

              <button type="button"
                      class="btn btn-success btn-mat"
                      data-bs-toggle="modal"
                      data-bs-target="#employeeModal"
                      title="Buscar empleado">
                <i class="bi bi-search"></i>
                <span class="d-none d-sm-inline">Buscar</span>
              </button>
            </div>

            @error('employee_user_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            <div class="form-text text-muted-500">
              Elegí del listado o usá <strong>Buscar</strong> para encontrar por nombre, CUIL o email.
            </div>

            {{-- Feedback visual del seleccionado --}}
            <div id="selectedEmployeePreview" class="mt-2 text-muted small" style="display:none;"></div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Puntos a consumir</label>
            <input type="number" min="1" name="points"
                   value="{{ old('points', 1) }}"
                   class="form-control @error('points') is-invalid @enderror" required>
            @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nota (opcional)</label>
          <textarea name="note" rows="3"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Detalle del gasto...">{{ old('note') }}</textarea>
          @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-grid">
          <button class="btn btn-primary btn-mat" type="submit">
            <i class="bi bi-qr-code me-1"></i> Generar comprobante / QR
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ===================== Modal Buscador de Empleados ===================== --}}
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content mat-card">
      <div class="modal-header mat-header">
        <h5 class="modal-title mat-title" id="employeeModalLabel">
          <i class="bi bi-person-search"></i> Buscar empleado
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="employeeSearchInput" type="text" class="form-control" placeholder="Nombre, CUIL o email…">
        </div>

        <div class="muted-hint mb-2">
          Tip: podés escribir parte del nombre o números del CUIL.
        </div>

        <div id="employeeResults" class="list-group">
          @foreach($employees as $e)
            <button type="button"
                    class="list-group-item list-group-item-action employee-result"
                    data-id="{{ $e->id }}"
                    data-name="{{ $e->name }}"
                    data-cuil="{{ $e->cuil ?? '' }}"
                    data-email="{{ $e->email ?? '' }}">
              <div class="d-flex w-100 justify-content-between">
                <strong>{{ $e->name }}</strong>
                <small class="text-muted">{{ $e->cuil ?? '' }}</small>
              </div>
              @if(!empty($e->email))
                <div class="text-muted small">{{ $e->email }}</div>
              @endif
            </button>
          @endforeach
        </div>

        <div id="employeeEmpty" class="text-center py-4 text-muted" style="display:none;">
          <i class="bi bi-inbox"></i> Sin resultados
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

{{-- ============================
   MODAL: Loading al enviar consumo
   ============================ --}}
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-body p-4 text-center">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
        <div class="mt-3 fw-semibold">Generando consumo…</div>
        <div class="text-muted small">Registrando y enviando correos. No cierres esta ventana.</div>
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

  // ============================
  // Loading modal al enviar form (SIEMPRE)
  // ============================
  const formEl = document.getElementById('redeemForm');

function showFallbackOverlay(){
  // overlay simple (sin bootstrap)
  let ov = document.getElementById('loadingOverlayFallback');
  if (ov) return;

  ov = document.createElement('div');
  ov.id = 'loadingOverlayFallback';
  ov.style.position = 'fixed';
  ov.style.inset = '0';
  ov.style.background = 'rgba(0,0,0,.35)';
  ov.style.display = 'flex';
  ov.style.alignItems = 'center';
  ov.style.justifyContent = 'center';
  ov.style.zIndex = '99999';
  ov.innerHTML = `
    <div style="background:#fff;border-radius:16px;padding:20px 22px;min-width:280px;text-align:center;">
      <div class="spinner-border" role="status" aria-hidden="true"></div>
      <div style="margin-top:12px;font-weight:700;">Generando consumo…</div>
      <div style="font-size:12px;color:#6c757d;margin-top:4px;">Registrando y enviando correos.</div>
    </div>
  `;
  document.body.appendChild(ov);
}

if (formEl) {
  let submitting = false;

  formEl.addEventListener('submit', (e) => {
    if (submitting) return;
    submitting = true;

    const modalEl = document.getElementById('loadingModal');

    // 1) si existe bootstrap.Modal -> modal
    if (modalEl && window.bootstrap?.Modal) {
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      e.preventDefault();
      requestAnimationFrame(() => setTimeout(() => formEl.submit(), 80));
      return;
    }

    // 2) fallback overlay (sin bootstrap JS)
    showFallbackOverlay();
    // acá NO prevenimos submit: dejamos que navegue normal
  });
}


  // ============================
  // Buscador de empleados (solo si existen elementos)
  // ============================
  const employeeModalEl = document.getElementById('employeeModal');
  const inputEl         = document.getElementById('employeeSearchInput');
  const listEl          = document.getElementById('employeeResults');
  const emptyEl         = document.getElementById('employeeEmpty');
  const selectEl        = document.getElementById('employeeSelect');
  const previewEl       = document.getElementById('selectedEmployeePreview');

  const hasEmployeeModal = employeeModalEl && inputEl && listEl && emptyEl && selectEl;
  if (!hasEmployeeModal) return;

  function normalize(s){ return String(s || '').toLowerCase().trim(); }
  function onlyDigits(s){ return String(s || '').replace(/\D/g, ''); }

  function updatePreview(){
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt || !opt.value) {
      if (previewEl) previewEl.style.display = 'none';
      return;
    }

    const name  = opt.dataset.name || opt.textContent || '';
    const cuil  = opt.dataset.cuil || '';
    const email = opt.dataset.email || '';

    if (previewEl) {
      previewEl.innerHTML = `
        <i class="bi bi-person-check me-1"></i>
        Seleccionado: <strong>${name}</strong>
        ${cuil ? ` · <span class="text-muted">CUIL ${cuil}</span>` : ''}
        ${email ? ` · <span class="text-muted">${email}</span>` : ''}
      `;
      previewEl.style.display = '';
    }
  }

  function filterEmployees(term){
    term = normalize(term);
    const termDigits = onlyDigits(term);
    let visible = 0;

    listEl.querySelectorAll('.employee-result').forEach(btn => {
      const name  = normalize(btn.dataset.name);
      const cuil  = onlyDigits(btn.dataset.cuil);
      const email = normalize(btn.dataset.email);

      const match =
        !term ||
        name.includes(term) ||
        email.includes(term) ||
        (termDigits && cuil.includes(termDigits));

      btn.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    emptyEl.style.display = (visible === 0) ? 'block' : 'none';
  }

  employeeModalEl.addEventListener('show.bs.modal', () => {
    inputEl.value = '';
    emptyEl.style.display = 'none';
    listEl.querySelectorAll('.employee-result').forEach(btn => btn.style.display = '');
    setTimeout(() => inputEl.focus(), 250);
  });

  inputEl.addEventListener('input', () => filterEmployees(inputEl.value));

  listEl.addEventListener('click', (e) => {
    const btn = e.target.closest('.employee-result');
    if (!btn) return;

    const id = String(btn.dataset.id || '');
    selectEl.value = id;
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    updatePreview();

    // Cerrar modal (sin hacks de backdrop)
    const instance = bootstrap.Modal.getOrCreateInstance(employeeModalEl);
    instance.hide();
  });

  selectEl.addEventListener('change', updatePreview);
  updatePreview();
});
</script>

@endpush
@endsection
