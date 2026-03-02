{{-- resources/views/points/empleado-detalle.blade.php --}}
@extends('layouts.app')

@section('title', 'Puntos de - '.$employee->name)

@section('content')

<style>
  .summary-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
  }
  .summary-icon { font-size: 2rem; opacity: .85; }
  .stats-number { font-size: 1.6rem; font-weight: 800; margin-bottom: .15rem; }
  .stats-label { font-size: .9rem; color: #6c757d; }

  .points-positive { color:#198754; font-weight:700; }
  .points-negative { color:#dc3545; font-weight:700; }
  .points-neutral  { color:#6c757d; font-weight:700; }

  .type-badge {
    font-size: .75rem;
    padding: .2rem .55rem;
    border-radius: 50rem;
    display: inline-block;
  }
  .badge-earn   { background:#d1e7dd; color:#0f5132; }
  .badge-redeem { background:#f8d7da; color:#842029; }
  .badge-adjust { background:#cff4fc; color:#055160; }
  .badge-expire { background:#e2e3e5; color:#41464b; }

   /* ====== PRINT ====== */
  @media print {
    /* ocultar elementos marcados */
    .no-print { display: none !important; }

    /* ocultar navbar / sidebar típicos (por si tu layout los usa) */
    header, nav, aside, .navbar, .sidebar, .offcanvas, .pc-sidebar, .pc-topbar {
      display: none !important;
    }

    /* sacar paddings y sombras para papel */
    body { background: #fff !important; }
    .card, .mat-card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .container, .container-fluid { width: 100% !important; max-width: 100% !important; }
  }

</style>

@php
  $available = (int)($totals['available'] ?? 0);
@endphp

<div class="card mat-card">
  <div class="mat-header">
    <div>
      <h3 class="mat-title mb-0">
        <i class="bi bi-person"></i> {{ $employee->name }}
      </h3>
      <div class="text-muted small">
        {{ $employee->email ?? '—' }}
        @if(!empty($employee->cuil)) · CUIL: {{ $employee->cuil }} @endif
        @if($employee->company) · {{ $employee->company->name }} @endif
      </div>
    </div>

<div class="ms-auto d-flex gap-2 no-print">
  <button type="button" class="btn btn-outline-secondary btn-mat" onclick="window.print()">
    <i class="bi bi-printer"></i> Imprimir
  </button>

  <a href="{{ route('points.resumen', request()->only(['company_id'])) }}" class="btn btn-outline-secondary btn-mat">
    <i class="bi bi-arrow-left"></i> Atrás
  </a>

  <a href="{{ route('points.index', ['employee_id' => $employee->id]) }}" class="btn btn-outline-secondary btn-mat">
    <i class="bi bi-list-ul"></i> Movimientos (Admin)
  </a>
</div>
  </div>

  <div class="card-body">

    {{-- Totales (colapsable) --}}
<div class="d-flex align-items-center mb-2 no-print">
  <button class="btn btn-outline-secondary btn-mat btn-sm"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#employeeTotals"
          aria-expanded="false"
          aria-controls="employeeTotals">
    <i class="bi bi-chevron-down me-1"></i> Ver totales
  </button>
</div>

<div class="collapse" id="employeeTotals">
  <div class="row g-3 mb-4">

    <div class="col-12 col-md-4">
      <div class="card summary-card h-100">
        <div class="card-body text-center">
          <div class="text-info"><i class="bi bi-wallet2 summary-icon"></i></div>
          <div class="stats-number">
            <span class="{{ $available > 0 ? 'points-positive' : ($available < 0 ? 'points-negative' : 'points-neutral') }}">
              {{ number_format($available) }}
            </span>
          </div>
          <div class="stats-label">Disponible</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card summary-card h-100">
        <div class="card-body text-center">
          <div class="text-success"><i class="bi bi-arrow-up-circle summary-icon"></i></div>
          <div class="stats-number">{{ number_format($totals['total_earned'] ?? 0) }}</div>
          <div class="stats-label">Ganado</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card summary-card h-100">
        <div class="card-body text-center">
          <div class="text-danger"><i class="bi bi-arrow-down-circle summary-icon"></i></div>
          <div class="stats-number">{{ number_format($totals['total_redeemed'] ?? 0) }}</div>
          <div class="stats-label">Canjeado</div>
        </div>
      </div>
    </div>

  </div>
</div>



    {{-- Filtros --}}
    <form method="GET" class="row g-3 mb-3" action="{{ route('points.employee.detail', $employee->id) }}">
      <div class="col-12 col-md-3">
        <label class="form-label">Tipo</label>
        <select name="type" class="form-select">
          <option value="">Todos</option>
@foreach(['earn','redeem','adjust','expire'] as $t)
  @php $label = config("points.types.$t") ?? ucfirst($t); @endphp
  <option value="{{ $t }}" @selected(request('type') === $t)>{{ $label }}</option>
@endforeach
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Fecha de inicio</label>
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Fecha de finalización</label>
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
      </div>

      <div class="col-12 col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary btn-mat" type="submit">
          <i class="bi bi-funnel"></i> Aplicar
        </button>
        @if(request()->anyFilled(['type','start_date','end_date']))
          <a class="btn btn-outline-secondary btn-mat" href="{{ route('points.employee.detail', $employee->id) }}">
            <i class="bi bi-x-lg"></i> Limpiar
          </a>
        @endif
      </div>
    </form>

    {{-- Tabla --}}
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th class="text-end">Puntos</th>
            <th>Negocio (donde consumió)</th>
            <th>Referencia</th>
            <th>Notas</th>
            <th>Creado por</th>
          </tr>
        </thead>
        <tbody>
          @forelse($points as $m)
            @php
              $typeClass = [
                'earn' => 'badge-earn',
                'redeem' => 'badge-redeem',
                'adjust' => 'badge-adjust',
                'expire' => 'badge-expire',
              ][$m->type] ?? 'badge-secondary';
            @endphp

            <tr>
              <td>
                <div class="fw-semibold">{{ optional($m->occurred_at)->format('Y-m-d') }}</div>
                <small class="text-muted">{{ optional($m->occurred_at)->format('H:i') }}</small>
              </td>

              <td>
                <span class="type-badge {{ $typeClass }}">
  {{ config("points.types.{$m->type}") ?? ucfirst($m->type) }}
</span>

              </td>

              <td class="text-end">
                <span class="{{ ($m->points ?? 0) >= 0 ? 'points-positive' : 'points-negative' }}">
                  {{ ($m->points ?? 0) >= 0 ? '+' : '' }}{{ number_format($m->points ?? 0) }}
                </span>
                @if(!is_null($m->money_amount))
                  <div class="small text-muted">${{ number_format((float)$m->money_amount, 2, ',', '.') }}</div>
                @endif
              </td>

              {{-- ✅ Negocio solo visible cuando corresponde (redeem de negocio) --}}
              <td>
                @if(!empty($m->business_user_id))
                  <div class="fw-semibold">{{ $m->business->name ?? '—' }}</div>
                  <small class="text-muted">Consumo en negocio</small>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

<td>
  @if($m->pointReference?->name)
    <span class="badge bg-light text-dark">{{ $m->pointReference->name }}</span>
  @elseif(!empty($m->reference))
    <span class="badge bg-light text-dark">{{ $m->reference }}</span>
  @else
    <span class="text-muted">—</span>
  @endif
</td>


              <td>
                @if($m->note)
                  <span title="{{ $m->note }}">{{ \Illuminate\Support\Str::limit($m->note, 40) }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                <small>{{ $m->createdBy->name ?? '—' }}</small>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <div class="mt-2">No se encontraron movimientos</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Mostrando {{ $points->firstItem() ?? 0 }}–{{ $points->lastItem() ?? 0 }}
        de {{ $points->total() }} movimientos
      </small>
      {{ $points->withQueryString()->links() }}
    </div>

  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('employeeTotals');
  if (!el) return;

  const key = 'mp_points_employeeTotals_open';
  const saved = localStorage.getItem(key);

  if (saved === '1') el.classList.add('show');

  el.addEventListener('shown.bs.collapse', () => localStorage.setItem(key, '1'));
  el.addEventListener('hidden.bs.collapse', () => localStorage.setItem(key, '0'));
});
</script>
@endpush


@endsection
