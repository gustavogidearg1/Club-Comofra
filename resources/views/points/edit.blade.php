@extends('layouts.app')

@section('title', 'Editar movimiento')

@push('styles')
<style>
  .mat-card{
    border-radius:16px;
    box-shadow:0 6px 18px rgba(15,23,42,.12);
    border:0;
  }
  .mat-header{
    display:flex;
    align-items:center;
    gap:.75rem;
    padding:.9rem 1rem;
    border-bottom:1px solid rgba(0,0,0,.06);
    background:transparent;
  }
  .mat-title{
    font-weight:800;
    font-size:1.05rem;
    margin:0;
    color:#0f172a;
  }
  .btn-mat{
    border-radius:999px;
    padding:.5rem .95rem;
    font-weight:700;
  }
</style>
@endpush

@section('content')
<x-flash />

<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title">
      <i class="bi bi-pencil-square me-1"></i> Editar movimiento #{{ $movement->id }}
    </h3>

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="card-body">
    <div class="alert alert-warning">
      <div class="fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i> Atención</div>
      Estás por corregir un movimiento de puntos. Usalo solo si hubo un error.
    </div>

    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6">
        <div class="border rounded-3 p-3">
          <div class="text-muted small">Empleado</div>
          <div class="fw-semibold">{{ $movement->employee?->name ?? '—' }}</div>
          <div class="text-muted small">Empresa: {{ $movement->company?->name ?? '—' }}</div>
          <div class="text-muted small">Creado por: {{ $movement->createdBy?->name ?? '—' }}</div>
        </div>
      </div>
    </div>

<form method="POST" action="{{ route('points.update', $movement) }}" class="row g-3" id="pointsEditForm">
...
      @csrf
      @method('PUT')

      <div class="col-12 col-md-3">
        <label class="form-label">Tipo</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
          @foreach($types as $k => $label)
            <option value="{{ $k }}" @selected(old('type', $movement->type) === $k)>{{ $label }}</option>
          @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Puntos</label>
        <input type="number"
               min="1"
               name="points"
               class="form-control @error('points') is-invalid @enderror"
               value="{{ old('points', abs((int)$movement->points)) }}"
               required>
        <div class="form-text">Ingresá siempre positivo. El sistema aplica el signo según el tipo.</div>
        @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Fecha y hora</label>
        <input type="datetime-local"
               name="occurred_at"
               class="form-control @error('occurred_at') is-invalid @enderror"
               value="{{ old('occurred_at', optional($movement->occurred_at)->format('Y-m-d\TH:i')) }}">
        @error('occurred_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Referencia <span class="text-danger">*</span></label>
        <select name="reference_id" class="form-select @error('reference_id') is-invalid @enderror" required>
          <option value="">Seleccionar referencia…</option>
          @foreach($references as $ref)
            <option value="{{ $ref->id }}" @selected((int)old('reference_id', $currentRefId ?? 0) === (int)$ref->id)>
              {{ $ref->name }}
            </option>
          @endforeach
        </select>
        @error('reference_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label class="form-label">Nota (opcional)</label>
        <textarea name="note"
                  rows="3"
                  class="form-control @error('note') is-invalid @enderror"
                  maxlength="500"
                  placeholder="Detalle interno (opcional)">{{ old('note', $movement->note) }}</textarea>
        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 d-flex gap-2">
        ...
<button type="submit" class="btn btn-primary btn-mat" id="btnSubmitEdit">
          <i class="bi bi-save"></i> Guardar cambios
        </button>
        <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Guardando + enviando email --}}
<div class="modal fade" id="modalSubmittingEdit" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-body p-4">
        <div class="d-flex align-items-start gap-3">
          <div class="spinner-border" role="status" aria-label="Guardando"></div>

          <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:1.05rem;">Guardando cambios…</div>
            <div class="text-muted mt-1">
              Por favor <b>no cierres esta pantalla</b>. Se está actualizando el movimiento y enviando el correo.
            </div>
            <div class="text-muted small mt-2">
              Esto puede demorar unos segundos.
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
  const form = document.getElementById('pointsEditForm');
  const btn  = document.getElementById('btnSubmitEdit');
  const modalEl = document.getElementById('modalSubmittingEdit');
  if(!form || !btn || !modalEl) return;

  let submitting = false;

  form.addEventListener('submit', function(ev){
    if(submitting){
      ev.preventDefault();
      return;
    }
    submitting = true;

    btn.disabled = true;
    btn.classList.add('disabled');

    // Mostrar modal (sin depender de bootstrap global)
    const tmp = document.createElement('button');
    tmp.type = 'button';
    tmp.setAttribute('data-bs-toggle','modal');
    tmp.setAttribute('data-bs-target','#modalSubmittingEdit');
    tmp.style.display = 'none';
    document.body.appendChild(tmp);
    tmp.click();
    tmp.remove();
  });

  window.addEventListener('pageshow', function(){
    submitting = false;
    btn.disabled = false;
    btn.classList.remove('disabled');
  });
})();
</script>
@endpush

<div class="modal fade" id="modalSubmittingEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body py-4">
        <div class="d-flex align-items-center gap-3">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <div>
            <div class="fw-semibold">Guardando cambios…</div>
            <div class="text-muted small">No cierres esta pantalla. Estamos actualizando el movimiento y enviando el correo.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
