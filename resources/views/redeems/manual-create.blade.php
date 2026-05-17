@extends('layouts.app')
@section('title','Consumo manual')

@section('content')
<style>
  .muted-hint { color:#6c757d; font-size:.9rem; }

  .saldo-chip{
    display:inline-flex; align-items:center; gap:.5rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }

  input.is-invalid {
  border-width: 2px;
}

.invalid-feedback {
  font-size: 0.9rem;
}

</style>

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header">
      <h3 class="mat-title mb-0">
        <i class="bi bi-shop me-2"></i> Consumo manual (Empleado → Negocio)
      </h3>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="saldo-chip" title="Saldo disponible">
          <i class="bi bi-wallet2"></i> Saldo: <strong id="saldoVal">{{ number_format($saldo) }}</strong>
        </span>

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

      <form method="POST" action="{{ route('redeems.manual.store', $business->id) }}" id="manualForm">
        @csrf

        <div class="mb-3">
          <label class="form-label fw-semibold">Negocio</label>
          <input class="form-control" value="{{ $business->name }}" readonly>
        </div>

        <input type="hidden" name="business_user_id" value="{{ $business->id }}">

        <div class="mb-3">
          <label class="form-label fw-semibold">Puntos a consumir</label>
          <input id="pointsInput"
       type="text"
       inputmode="numeric"
       name="points"
       class="form-control @error('points') is-invalid @enderror"
       value="{{ old('points') }}"
       required>
          @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <div id="warnSaldo" class="alert alert-warning mt-2 mb-0" style="display:none;">
            El valor ingresado supera tu saldo disponible.
          </div>

          <div class="form-text muted-hint">
            Se descontarán <strong id="restara">0</strong> puntos.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nota (opcional)</label>
          <textarea name="note"
                    class="form-control @error('note') is-invalid @enderror"
                    rows="3">{{ old('note') }}</textarea>
          @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button id="submitBtn" class="btn btn-primary btn-mat w-100" type="submit">
          Confirmar consumo
        </button>
      </form>
    </div>
  </div>
</div>

{{-- MODAL: Enviando consumo --}}
<div class="modal fade" id="modalSubmittingRedeem" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-body p-4">
        <div class="d-flex align-items-start gap-3">
          <div class="spinner-border" role="status" aria-label="Enviando"></div>

          <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:1.05rem;">Procesando el consumo…</div>
            <div class="text-muted mt-1">
              Por favor <b>no cierres esta pantalla</b>. Esto puede demorar unos segundos.
            </div>
            <div class="text-muted small mt-2">
              Si refrescás o volvés atrás, el consumo podría no guardarse correctamente.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
(function(){
  const form      = document.getElementById('manualForm');
  const submitBtn = document.getElementById('submitBtn');
  const modalEl   = document.getElementById('modalSubmittingRedeem');
  const input     = document.getElementById('pointsInput');
  const saldoEl   = document.getElementById('saldoVal');
  const restaraEl = document.getElementById('restara');
  const warnSaldo = document.getElementById('warnSaldo');

  if (!form || !submitBtn || !modalEl || !input || !saldoEl) return;

  let submitting = false;
  const saldo = parseFloat(String(saldoEl.innerText).replace(/\./g, '').replace(',', '.').replace(/[^\d.]/g, '') || 0);

  function normalizeNumber(value) {
    value = String(value || '').trim();

    // deja solo números, punto y coma
    value = value.replace(/[^\d.,]/g, '');

    // si usa coma decimal: 65000,22 o 65.000,22
    if (value.includes(',')) {
      value = value.replace(/\./g, '').replace(',', '.');
    }

    return value;
  }

  function getNumericValue() {
    const normalized = normalizeNumber(input.value);
    return parseFloat(normalized);
  }

  function formatDisplay(value) {
    const normalized = normalizeNumber(value);
    const number = parseFloat(normalized);

    if (isNaN(number)) return '0,00';

    return number.toLocaleString('es-AR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function showError(msg) {
    input.classList.add('is-invalid');

    let feedback = input.parentNode.querySelector('.invalid-feedback-custom');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback invalid-feedback-custom';
      input.parentNode.appendChild(feedback);
    }

    feedback.innerText = msg;
    feedback.style.display = 'block';
    input.focus();
  }

  function clearError() {
    input.classList.remove('is-invalid');

    const feedback = input.parentNode.querySelector('.invalid-feedback-custom');
    if (feedback) feedback.style.display = 'none';
  }

  input.addEventListener('input', function () {
    // NO formateamos el input mientras escribe
    input.value = input.value.replace(/[^\d.,]/g, '');

    const value = getNumericValue();

    if (restaraEl) {
      restaraEl.innerText = formatDisplay(input.value);
    }

    if (warnSaldo) {
      warnSaldo.style.display = !isNaN(value) && value > saldo ? 'block' : 'none';
    }

    clearError();
  });

  input.addEventListener('blur', function() {
    const value = getNumericValue();

    if (!input.value || isNaN(value) || value <= 0) {
      showError('Debe ser mayor a 0');
      return;
    }

    // al salir del campo recién lo mostramos lindo
    input.value = formatDisplay(input.value);
  });

  input.addEventListener('focus', function() {
    // al volver a editar, lo dejamos en formato editable
    input.value = normalizeNumber(input.value);
  });

  form.addEventListener('submit', function(ev){

    if (submitting) {
      ev.preventDefault();
      return;
    }

    const value = getNumericValue();

    if (!input.value || isNaN(value) || value <= 0) {
      ev.preventDefault();
      showError('Ingresá puntos válidos (mayor a 0)');
      return;
    }

    if (value > saldo) {
      ev.preventDefault();
      showError('No podés consumir más que tu saldo disponible');
      return;
    }

    if (!form.checkValidity()) {
      return;
    }

    if (submitBtn.disabled) {
      ev.preventDefault();
      return;
    }

    // Laravel recibe 65000.22
    input.value = normalizeNumber(input.value);

    submitting = true;

    submitBtn.disabled = true;
    submitBtn.classList.add('disabled');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Enviando…';

    const tmp = document.createElement('button');
    tmp.type = 'button';
    tmp.setAttribute('data-bs-toggle', 'modal');
    tmp.setAttribute('data-bs-target', '#modalSubmittingRedeem');
    tmp.style.display = 'none';
    document.body.appendChild(tmp);
    tmp.click();
    tmp.remove();
  });

  window.addEventListener('pageshow', function(){
    submitting = false;
    submitBtn.disabled = false;
    submitBtn.classList.remove('disabled');
    submitBtn.innerHTML = 'Confirmar consumo';
  });

})();
</script>
@endpush


@endsection
