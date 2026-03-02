@extends('layouts.app')

@section('title','Previsualización Importación')

@push('styles')
<style>
  .submit-overlay {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(1.5px);
  }
  .submit-card {
    background: #fff;
    border-radius: 14px;
    padding: 24px 28px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,.2);
    max-width: 460px;
    width: 92%;
  }
  .submit-card .muted {
    color:#6c757d;
    font-size: .95rem;
  }
</style>
@endpush

@section('content')
  <x-page-header title="Previsualización de Importación" />
  <x-flash />

  <div class="card mat-card mb-3">
    <div class="card-body">
      <div class="d-flex flex-wrap gap-3 align-items-center">
        <div>
          <div class="fw-semibold">Lote #{{ $batch->id }}</div>
          <div class="text-muted small">Archivo: {{ $batch->filename }}</div>
        </div>

        <div class="ms-auto d-flex gap-2">
          <span class="badge text-bg-secondary">Total: {{ $batch->rows_total }}</span>
          <span class="badge text-bg-success">OK: {{ $batch->rows_ok }}</span>
          <span class="badge text-bg-danger">Error: {{ $batch->rows_error }}</span>
        </div>
      </div>

      <hr>

      <form method="POST" action="{{ route('points.import.commit') }}" id="commitImportForm">
        @csrf
        <input type="hidden" name="confirm" value="1">

        <button
          type="submit"
          id="commitImportBtn"
          class="btn btn-success btn-mat"
          @if($batch->rows_ok == 0) disabled @endif
        >
          <i class="bi bi-check2-circle me-1"></i>
          Confirmar Importación
        </button>

        <a href="{{ route('points.import.create') }}" class="btn btn-outline-secondary ms-2">
          Volver
        </a>

        @if($batch->rows_ok == 0)
          <div class="text-danger small mt-2">No hay filas válidas para importar.</div>
        @endif
      </form>
    </div>
  </div>

  <div class="card mat-card">
    <div class="card-body">
      <h6 class="mb-3">Detalle (primeras filas leídas)</h6>

      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Línea</th>
              <th>CUIL/CUIT</th>
              <th>Puntos</th>
              <th>Fecha</th>
              <th>Ref</th>
              <th>Estado</th>
              <th>Observaciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($preview as $r)
              <tr>
                <td>{{ $r['line'] }}</td>
                <td>{{ $r['employee_cuil'] ?? '—' }}</td>
                <td>{{ $r['points'] ?? '—' }}</td>
                <td>{{ $r['occurred_at'] ?? '—' }}</td>
                <td>{{ $r['reference'] ?? '—' }}</td>
                <td>
                  @if(($r['status'] ?? '') === 'ok')
                    <span class="badge text-bg-success">OK</span>
                  @else
                    <span class="badge text-bg-danger">Error</span>
                  @endif
                </td>
                <td class="text-muted small">
                  @if(!empty($r['issues']))
                    {{ implode(' | ', $r['issues']) }}
                  @else
                    —
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>
  </div>

  {{-- Overlay --}}
  <div id="importOverlay" class="submit-overlay">
    <div class="submit-card">
      <div class="spinner-border" role="status" style="width:4rem;height:4rem;"></div>
      <h5 class="mt-3 mb-1">Procesando importación…</h5>
      <div class="muted">
        Se están cargando los movimientos y enviando las notificaciones.<br>
        Por favor, esperá sin cerrar esta ventana.
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
(function () {
  const overlay = document.getElementById('importOverlay');
  const form = document.getElementById('commitImportForm');
  const btn  = document.getElementById('commitImportBtn');
  if (!form || !overlay) return;

  form.addEventListener('submit', function () {
    // Si el browser detecta inválidos, no mostrar overlay
    if (!form.checkValidity()) return;

    if (btn) {
      btn.disabled = true;
      btn.dataset._oldHtml = btn.innerHTML;
      btn.innerHTML = 'Confirmando…';
    }

    overlay.style.display = 'flex';
  });
})();
</script>
@endpush
