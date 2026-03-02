@extends('layouts.app')

@section('title', 'Resumen de puntos')

@push('styles')
<style>
  .summary-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
  }
  .summary-icon { font-size: 2rem; opacity: .85; }
  .stats-number { font-size: 1.6rem; font-weight: 800; margin-bottom: .15rem; }
  .stats-label  { font-size: .9rem; color: #6c757d; }
  .points-positive { color:#198754; font-weight:700; }
  .points-negative { color:#dc3545; font-weight:700; }
  .points-neutral  { color:#6c757d; font-weight:700; }
</style>
@endpush

@section('content')
<div class="card mat-card">
  <div class="mat-header d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
    <h3 class="mat-title mb-0">
      <i class="bi bi-bar-chart"></i> Resumen de puntos
    </h3>

    <div class="ms-md-auto d-flex gap-2">
      <a href="{{ route('points.index', request()->query()) }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-list-ul"></i> Movimientos
      </a>
    </div>
  </div>

  <div class="card-body">

    {{-- Filtros --}}
    <form method="GET" class="row g-3 mb-3" action="{{ route('points.resumen') }}">

      @if($isSiteAdmin)
        <div class="col-12 col-md-3">
          <label class="form-label">Compañía</label>
          <select name="company_id" class="form-select">
            <option value="">All Companies</option>
            @foreach($companies as $c)
              <option value="{{ $c->id }}" @selected((string)$companyId === (string)$c->id)>
                {{ $c->name }}
              </option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="col-12 col-md-4">
        <label class="form-label">Buscar empleado</label>
        <input type="text"
               name="employee_q"
               class="form-control"
               value="{{ request('employee_q') }}"
               placeholder="Nombre o email...">
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">CUIL</label>
        <input type="text"
               name="cuil"
               class="form-control"
               value="{{ request('cuil') }}"
               placeholder="20-xxxxxxxx-x o 20xxxxxxxxx">
      </div>

      <div class="col-12 col-md-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary btn-mat w-100" type="submit">
          <i class="bi bi-funnel"></i> Filtrar
        </button>
      </div>

      <div class="col-12 d-flex gap-2">
        @if(request()->anyFilled(['company_id','employee_q','cuil']))
          <a href="{{ route('points.resumen') }}" class="btn btn-outline-secondary btn-mat">
            <i class="bi bi-x-lg"></i> Limpiar
          </a>
        @endif

        {{-- Botón collapse de totales --}}
        <button class="btn btn-outline-secondary btn-mat"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#overallTotalsBox"
                aria-expanded="false"
                aria-controls="overallTotalsBox">
          <i class="bi bi-chevron-down me-1"></i> Ver totales
        </button>
      </div>

    </form>

    {{-- Totales generales (colapsable) --}}
    <div class="collapse" id="overallTotalsBox">
      <div class="row g-3 mb-4">

        <div class="col-12 col-md-3">
          <div class="card summary-card h-100">
            <div class="card-body text-center">
              <div class="text-primary"><i class="bi bi-people summary-icon"></i></div>
              <div class="stats-number">{{ number_format($overallTotals['total_employees'] ?? 0) }}</div>
              <div class="stats-label">Empleados</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card summary-card h-100">
            <div class="card-body text-center">
              <div class="text-info"><i class="bi bi-wallet2 summary-icon"></i></div>
              <div class="stats-number">{{ number_format($overallTotals['total_points_available'] ?? 0) }}</div>
              <div class="stats-label">Disponible</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card summary-card h-100">
            <div class="card-body text-center">
              <div class="text-success"><i class="bi bi-arrow-up-circle summary-icon"></i></div>
              <div class="stats-number">{{ number_format($overallTotals['total_points_earned'] ?? 0) }}</div>
              <div class="stats-label">Ganado</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card summary-card h-100">
            <div class="card-body text-center">
              <div class="text-danger"><i class="bi bi-arrow-down-circle summary-icon"></i></div>
              <div class="stats-number">{{ number_format($overallTotals['total_points_redeemed'] ?? 0) }}</div>
              <div class="stats-label">Rendición</div>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- Tabla --}}
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Empleado</th>
            <th>Compañia</th>
            <th class="text-end">Ganado</th>
            <th class="text-end">Rendicion</th>
            <th class="text-end">Disponible</th>
            <th class="text-end">Movimientos</th>
            <th class="text-end">Accion</th>
          </tr>
        </thead>

        <tbody>
          @forelse($summary as $emp)
            @php $available = (int)($emp->total_available ?? 0); @endphp
            <tr>
              <td>
                <div class="fw-semibold">{{ $emp->name }}</div>
                <small class="text-muted">{{ $emp->email }}</small>
                @if(!empty($emp->cuil))
                  <div class="small text-muted">CUIL: {{ $emp->cuil }}</div>
                @endif
              </td>

              <td>
                <small class="text-muted">{{ $emp->company->name ?? '—' }}</small>
              </td>

              <td class="text-end">
                <span class="points-positive">+{{ number_format($emp->total_earned ?? 0) }}</span>
              </td>

              <td class="text-end">
                <span class="points-negative">-{{ number_format($emp->total_redeemed ?? 0) }}</span>
              </td>

              <td class="text-end">
                <span class="{{ $available > 0 ? 'points-positive' : ($available < 0 ? 'points-negative' : 'points-neutral') }}">
                  {{ number_format($available) }}
                </span>
              </td>

              <td class="text-end">
                <span class="badge bg-light text-dark">{{ number_format($emp->movement_count ?? 0) }}</span>
              </td>

              <td class="text-end">
                <a href="{{ route('points.employee.detail', $emp->id) }}" class="btn btn-sm btn-outline-secondary" title="View detail">
                  <i class="bi bi-person"></i> Detalle
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <div class="mt-2">No se encontraron empleados</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Showing {{ $summary->firstItem() ?? 0 }}–{{ $summary->lastItem() ?? 0 }}
        of {{ $summary->total() }} empleados
      </small>
      {{ $summary->withQueryString()->links() }}
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('overallTotalsBox');
  if (!el) return;

  const key = 'mp_points_summary_totals_open';
  const saved = localStorage.getItem(key);

  if (saved === '1') el.classList.add('show');

  el.addEventListener('shown.bs.collapse', () => localStorage.setItem(key, '1'));
  el.addEventListener('hidden.bs.collapse', () => localStorage.setItem(key, '0'));
});
</script>
@endpush
