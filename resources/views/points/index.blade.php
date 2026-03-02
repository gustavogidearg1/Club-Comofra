@extends('layouts.app')

@section('title', 'Gestión de puntos')

@section('content')

<style>
  .mat-sort { display:inline-flex; align-items:center; gap:.25rem; cursor:pointer; text-decoration:none; }
  .mat-sort .sort-icon { font-size:.9rem; opacity:.6; }
  .mat-sort.active { font-weight:600; }

  .points-positive { color:#198754; font-weight:600; }
  .points-negative { color:#dc3545; font-weight:600; }
  .points-neutral  { color:#6c757d; }

  .badge-points { font-size:0.9em; padding:0.25em 0.6em; }
  .badge-earn   { background-color:#d1e7dd; color:#0f5132; }
  .badge-redeem { background-color:#f8d7da; color:#842029; }
  .badge-adjust { background-color:#cff4fc; color:#055160; }
  .badge-expire { background-color:#e2e3e5; color:#41464b; }

  .summary-card{
    border-radius:10px; border:none;
    box-shadow:0 0.125rem 0.25rem rgba(0,0,0,.075);
    transition:transform .2s;
  }
  .summary-card:hover{ transform:translateY(-2px); }
  .summary-icon{ font-size:2rem; opacity:.8; }
  .stats-number{ font-size:1.8rem; font-weight:bold; margin-bottom:.25rem; }
  .stats-label{ font-size:.9rem; color:#6c757d; }

  .type-badge{
    font-size:.75rem;
    padding:.2rem .5rem;
    border-radius:50rem;
    display:inline-block;
  }
</style>

<div class="card mat-card">
<div class="mat-header d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
  <h3 class="mat-title mb-0">
    <i class="bi bi-trophy"></i> Gestión de puntos
  </h3>

  @if($isSiteAdmin || $isCompanyAdmin)
    <div class="ms-md-auto d-grid gap-2 d-md-flex">
      <a href="{{ route('points.create') }}" class="btn btn-primary btn-mat">
        <i class="bi bi-plus-lg"></i> Crear mov
      </a>

      <a href="{{ route('points.resumen') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-bar-chart"></i> Resumen
      </a>

<a href="{{ route('points.export', request()->query()) }}" class="btn btn-outline-success btn-mat">
  <i class="bi bi-download"></i> Exportar
</a>
    </div>
  @endif
</div>

  <div class="card-body">

    {{-- Estadísticas rápidas --}}
@if(isset($stats) && ($isSiteAdmin || $isCompanyAdmin))

  <div class="d-flex align-items-center mb-2">
    <button class="btn btn-outline-secondary btn-mat btn-sm"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#quickStats"
            aria-expanded="false"
            aria-controls="quickStats">
      <i class="bi bi-chevron-down me-1"></i> Ver estadísticas rápidas
    </button>
  </div>

  <div class="collapse" id="quickStats">
    <div class="row mb-4">

      <div class="col-md-2 col-6 mb-3">
        <div class="card summary-card h-100">
          <div class="card-body text-center">
            <div class="text-success"><i class="bi bi-wallet2 summary-icon"></i></div>
            <div class="stats-number">{{ number_format($stats['total_points']) }}</div>
            <div class="stats-label">Puntos totales</div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 mb-3">
        <div class="card summary-card h-100">
          <div class="card-body text-center">
            <div class="text-primary"><i class="bi bi-arrow-up-circle summary-icon"></i></div>
            <div class="stats-number">{{ number_format($stats['total_earned']) }}</div>
            <div class="stats-label">Puntos ganados</div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 mb-3">
        <div class="card summary-card h-100">
          <div class="card-body text-center">
            <div class="text-danger"><i class="bi bi-arrow-down-circle summary-icon"></i></div>
            <div class="stats-number">{{ number_format($stats['total_redeemed']) }}</div>
            <div class="stats-label">Puntos canjeados</div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 mb-3">
        <div class="card summary-card h-100">
          <div class="card-body text-center">
            <div class="text-info"><i class="bi bi-list-ul summary-icon"></i></div>
            <div class="stats-number">{{ number_format($stats['total_movements']) }}</div>
            <div class="stats-label">Movimientos totales</div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 mb-3">
        <div class="card summary-card h-100">
          <div class="card-body text-center">
            <div class="text-warning"><i class="bi bi-graph-up summary-icon"></i></div>
            <div class="stats-number">{{ number_format($stats['avg_points'] ?? 0, 1) }}</div>
            <div class="stats-label">Promedio por movimiento</div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endif


    {{-- Filtros --}}
    @if($isSiteAdmin || $isCompanyAdmin)
      <form method="GET" class="row g-3 mb-4" action="{{ route('points.index') }}">

        <div class="col-12 col-md-3">
          <label class="form-label">Buscar</label>
          <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                 placeholder="empleado, negocio, referencia, notas...">
        </div>

        @if($isSiteAdmin)
          <div class="col-12 col-md-2">
            <label class="form-label">Compañía</label>
            <select name="company_id" class="form-select">
              <option value="">Todas las empresas</option>
              @foreach($companies as $company)
                <option value="{{ $company->id }}" @selected((string)request('company_id') === (string)$company->id)>
                  {{ $company->name }}
                </option>
              @endforeach
            </select>
          </div>
        @endif



        <div class="col-12 col-md-2">
          <label class="form-label">Tipo</label>
          <select name="type" class="form-select">
            <option value="all">Todos los tipos</option>
            @foreach($types as $typeName)
              @php $lbl = config("points.types.$typeName") ?? ucfirst($typeName); @endphp
              <option value="{{ $typeName }}" @selected(request('type') == $typeName)>
                {{ $lbl }}
              </option>
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

        <div class="col-12 col-md-2">
          <label class="form-label">Por página</label>
          <select name="per" class="form-select">
            @foreach([15,25,50,100] as $opt)
              <option value="{{ $opt }}" @selected((int)request('per', 15) === (int)$opt)>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 d-flex gap-2 align-items-end">
          <button class="btn btn-primary btn-mat" type="submit">
            <i class="bi bi-funnel"></i> Aplicar filtros
          </button>

          @if(request()->anyFilled(['q','company_id','employee_id','type','batch_id','start_date','end_date']))
            <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
              <i class="bi bi-x-lg"></i> Limpiar
            </a>
          @endif
        </div>

      </form>
    @endif

    {{-- Tabla --}}
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>

            @if($isSiteAdmin || $isCompanyAdmin)
              <th>Empleado</th>
              <th>Negocio</th>
              <th>Confirmado por</th>
            @endif

            <th>Tipo</th>
            <th class="text-end">Puntos</th>
            <th>Referencia</th>
            <th>Notas</th>


              <th>Creado por</th>


            <th class="text-end">Acción</th>
          </tr>
        </thead>

        <tbody>
          @forelse($points as $point)
            @php
              $typeClass = [
                'earn'   => 'badge-earn',
                'redeem' => 'badge-redeem',
                'adjust' => 'badge-adjust',
                'expire' => 'badge-expire',
              ][$point->type] ?? 'badge-secondary';

              $typeText = config("points.types.{$point->type}") ?? ucfirst($point->type);

              $refName = $point->pointReference?->name ?? null;
              $refText = $refName ?: ($point->reference ?? null);
            @endphp

            <tr>
              <td>
                <div class="fw-semibold">{{ optional($point->occurred_at)->format('Y-m-d') }}</div>
                <small class="text-muted">{{ optional($point->occurred_at)->format('H:i') }}</small>
              </td>

              @if($isSiteAdmin || $isCompanyAdmin)
                <td>
                  <div class="fw-semibold">{{ $point->employee->name ?? '—' }}</div>
                  <small class="text-muted">{{ $point->employee->cuil ?? '—' }}</small>
                </td>

                <td>
                  <div class="fw-semibold">{{ $point->business->name ?? '—' }}</div>
                  <small class="text-muted">{{ $point->business_user_id ? 'Consumo en negocio' : '—' }}</small>
                </td>

                <td>
                  <small>{{ $point->confirmedBy->name ?? '—' }}</small>
                </td>
              @endif

              <td>
                <span class="type-badge {{ $typeClass }}">{{ $typeText }}</span>
              </td>

              <td class="text-end">
                <span class="{{ ($point->points ?? 0) >= 0 ? 'points-positive' : 'points-negative' }}">
                  {{ ($point->points ?? 0) >= 0 ? '+' : '' }}{{ number_format((int)($point->points ?? 0)) }}
                </span>

                @if(!is_null($point->money_amount))
                  <div class="small text-muted">
                    ${{ number_format((float)$point->money_amount, 2, ',', '.') }}
                  </div>
                @endif
              </td>

              <td>
                @if(!empty($refText))
                  <span class="badge bg-light text-dark">{{ $refText }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                @if(!empty($point->note))
                  <span title="{{ $point->note }}">{{ \Illuminate\Support\Str::limit($point->note, 30) }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              @if($isSiteAdmin || $isCompanyAdmin)
                <td>
                  <small>{{ $point->createdBy->name ?? '—' }}</small>
                </td>


              @endif

              <td class="text-end">



<div class="btn-group btn-group-sm" role="group">
  @if($isSiteAdmin || $isCompanyAdmin)
    <a href="{{ route('points.employee.detail', $point->employee_user_id) }}"
       class="btn btn-outline-secondary" title="Ver empleado">
      <i class="bi bi-person"></i>
    </a>

    <a href="{{ route('points.edit', $point->id) }}"
       class="btn btn-outline-primary" title="Editar movimiento">
      <i class="bi bi-pencil"></i>
    </a>
  @endif
</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ ($isSiteAdmin || $isCompanyAdmin) ? 11 : 6 }}" class="text-center text-muted py-4">
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
      {{ $points->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

  </div>
</div>

@if($isSiteAdmin || $isCompanyAdmin)
<script>
document.addEventListener('DOMContentLoaded', function () {
  const startDate = document.querySelector('input[name="start_date"]');
  const endDate   = document.querySelector('input[name="end_date"]');

  if (startDate && endDate) {
    startDate.addEventListener('change', function () {
      if (!endDate.value || endDate.value < this.value) endDate.value = this.value;
    });
  }

    const el = document.getElementById('quickStats');
  if (!el) return;

  const key = 'mp_points_quickStats_open';
  const saved = localStorage.getItem(key);

  if (saved === '1') el.classList.add('show');

  el.addEventListener('shown.bs.collapse', () => localStorage.setItem(key, '1'));
  el.addEventListener('hidden.bs.collapse', () => localStorage.setItem(key, '0'));

});
</script>
@endif

@endsection
