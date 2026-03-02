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
                 type="number"
                 min="1"
                 name="points"
                 class="form-control @error('points') is-invalid @enderror"
                 value="{{ old('points', 1) }}"
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

  if (!form || !submitBtn || !modalEl) return;

  let submitting = false;

  form.addEventListener('submit', function(ev){
    // evita doble submit
    if (submitting) {
      ev.preventDefault();
      return;
    }

    // si el HTML5 form no es válido, que el browser muestre los mensajes
    if (!form.checkValidity()) {
      return; // no preventDefault
    }

    // si el botón ya estaba disabled (por saldo insuficiente), no mandamos
    if (submitBtn.disabled) {
      ev.preventDefault();
      return;
    }

    submitting = true;

    // bloquea UI
    submitBtn.disabled = true;
    submitBtn.classList.add('disabled');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando…';

    // mostrar modal (sin depender de window.bootstrap)
    // truco: "click" a un botón temporal con data-bs-toggle
    const tmp = document.createElement('button');
    tmp.type = 'button';
    tmp.setAttribute('data-bs-toggle', 'modal');
    tmp.setAttribute('data-bs-target', '#modalSubmittingRedeem');
    tmp.style.display = 'none';
    document.body.appendChild(tmp);
    tmp.click();
    tmp.remove();

    // importante: no hacemos preventDefault -> dejamos que el submit siga normal
    // (si quisieras asegurar render del modal, podrías hacer preventDefault y luego form.submit() con setTimeout)
  });

  // Si el navegador vuelve atrás (bfcache), re-habilita el botón
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
