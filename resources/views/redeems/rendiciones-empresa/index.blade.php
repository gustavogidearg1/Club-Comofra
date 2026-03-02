@extends('layouts.app')
@section('title','Rendiciones a empresa')

@section('content')
<style>
  .chip{
    display:inline-flex; align-items:center; gap:.45rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }
  .small-muted{ color:#6c757d; font-size:.9rem; }
  .table td, .table th { vertical-align: middle; }
</style>

@php
  $u = auth()->user();
  $isSiteAdmin    = $u?->hasRole('admin_sitio') ?? false;
  $isCompanyAdmin = ($u?->hasRole('admin_empresa') ?? false) && !$isSiteAdmin;
  $isBusiness     = $u?->hasRole('negocio') ?? false;

  $estado = $estado ?? request('estado','pendiente');
  $mustPickBusiness = $mustPickBusiness ?? false;
@endphp

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header flex-wrap">
      <h3 class="mat-title mb-0 flex-grow-1" style="min-width: 240px;">
        <i class="bi bi-receipt me-2"></i> Rendiciones a empresa
      </h3>

      <div class="ms-auto d-flex gap-2 mt-2 mt-lg-0">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
      </div>
    </div>

    <div class="card-body p-4">
      <x-flash />

      {{-- ============ FILTROS ============ --}}
      <form method="GET" class="row g-3 mb-3">

        {{-- Negocio (solo admins) --}}
        @if($isSiteAdmin || $isCompanyAdmin)
          <div class="col-12 col-md-4">
            <label class="form-label">Negocio</label>
            <select name="negocio_id" class="form-select" required>
              <option value="">— Seleccionar negocio —</option>
              @foreach(($negocios ?? collect()) as $n)
                <option value="{{ $n->id }}" @selected((int)request('negocio_id') === (int)$n->id)>
                  {{ $n->name }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Para evitar errores, primero elegí un negocio.</div>
          </div>
        @endif

        <div class="col-12 col-md-4">
          <label class="form-label">Empleado</label>
          <select name="empleado_id" class="form-select" @disabled($mustPickBusiness)>
            <option value="">Todos</option>
            @foreach(($empleados ?? collect()) as $e)
              <option value="{{ $e->id }}" @selected((int)request('empleado_id') === (int)$e->id)>
                {{ $e->name }}{{ $e->cuil ? ' — '.$e->cuil : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label">Desde</label>
          <input type="date" name="desde" value="{{ request('desde') }}" class="form-control" @disabled($mustPickBusiness)>
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label">Hasta</label>
          <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control" @disabled($mustPickBusiness)>
        </div>

        <div class="col-12 col-md-2">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select" @disabled($mustPickBusiness)>
            <option value="pendiente" @selected($estado==='pendiente')>Pendiente</option>
            <option value="rendido"   @selected($estado==='rendido')>Rendido</option>
            <option value="todos"     @selected($estado==='todos')>Todos</option>
          </select>
        </div>

        <div class="col-12 col-md-2 d-flex align-items-end">
          <button class="btn btn-primary btn-mat w-100">
            <i class="bi bi-funnel"></i> Filtrar
          </button>
        </div>
      </form>

      @if($mustPickBusiness)
        <div class="alert alert-warning mat-alert">
          <i class="bi bi-exclamation-triangle"></i>
          Seleccioná un <strong>Negocio</strong> para listar consumos y crear rendiciones.
        </div>
      @endif

      {{-- Totales --}}
      <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="chip">
          <i class="bi bi-123"></i>
          Total puntos (página): {{ number_format($totalPuntos ?? 0, 0, ',', '.') }}
        </span>
      </div>

      {{-- ============ LISTADO + SELECCIÓN ============ --}}
      @if(!$mustPickBusiness)

        <form id="form-rendicion" method="POST" action="{{ route('redeems.rendiciones_empresa.store') }}">
          @csrf

          {{-- Seguridad extra: negocio elegido viaja al store (admins) --}}
          @if($isSiteAdmin || $isCompanyAdmin)
            <input type="hidden" name="negocio_id" value="{{ request('negocio_id') }}">
          @endif

          {{-- Período según filtros --}}
          <input type="hidden" name="period_from" value="{{ request('desde') }}">
          <input type="hidden" name="period_to"   value="{{ request('hasta') }}">

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button id="btnCrearRendicion" class="btn btn-success btn-mat" type="submit"
                    @disabled($estado === 'rendido')>
              <i class="bi bi-receipt"></i> Crear rendición con seleccionados
            </button>

            @if($estado === 'rendido')
              <span class="badge text-bg-secondary align-self-center">
                Estás viendo “Rendido” (selección deshabilitada)
              </span>
            @endif

            <span class="badge text-bg-light align-self-center" id="selInfo">
              Seleccionados: 0 • Puntos: 0
            </span>
          </div>

          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th style="width:40px;">
                    <input type="checkbox" id="checkAll" @disabled($estado === 'rendido')>
                  </th>
                  <th>#</th>
                  <th>Fecha</th>
                  <th>Empleado</th>
                  <th>Negocio</th>
                  <th class="text-end">Puntos</th>
                  <th>Referencia</th>
                  <th>Nota</th>
                  <th>Rendición</th>
                </tr>
              </thead>
              <tbody>
                @forelse($consumos as $c)
                  @php
                    $yaRendido = !empty($c->settlement_id);
                    $disabled = $yaRendido || ($estado === 'rendido');
                    $pts = (int)($c->points ?? 0);
                  @endphp
                  <tr>
                    <td>
                      <input type="checkbox"
                             name="redemption_ids[]"
                             value="{{ $c->id }}"
                             class="row-check"
                             data-points="{{ $pts }}"
                             @disabled($disabled)>
                    </td>
                    <td>{{ $c->id }}</td>
                    <td>{{ optional($c->movement?->occurred_at ?? $c->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->employee?->name ?? '-' }}</td>
                    <td>{{ $c->business?->name ?? '-' }}</td>
                    <td class="text-end fw-semibold">{{ number_format($pts, 0, ',', '.') }}</td>
                    <td>{{ $c->reference ?? '-' }}</td>
                    <td class="text-muted">{{ $c->note ?? '-' }}</td>
                    <td>
                      @if($yaRendido)
                        <a class="btn btn-sm btn-outline-primary"
                           href="{{ route('redeems.rendiciones_empresa.show', $c->settlement_id) }}">
                          <i class="bi bi-eye"></i> Ver #{{ $c->settlement_id }}
                        </a>
                      @else
                        <span class="badge text-bg-warning">Pendiente</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                      <i class="bi bi-inbox"></i> Sin resultados
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $consumos->links() }}
          </div>

        </form>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const checkAll = document.getElementById('checkAll');
  const form     = document.getElementById('form-rendicion');
  const selInfo  = document.getElementById('selInfo');

  const rows = () => Array.from(document.querySelectorAll('.row-check:not(:disabled)'));

  function updateInfo(){
    const enabled = rows();
    const selected = enabled.filter(ch => ch.checked);

    const count = selected.length;
    let pts = 0;
    selected.forEach(ch => pts += parseInt(ch.dataset.points || '0', 10) || 0);

    if (selInfo) {
      selInfo.textContent = `Seleccionados: ${count} • Puntos: ${pts.toLocaleString('es-AR')}`;
    }

    if (checkAll) {
      const allCount = enabled.length;
      checkAll.indeterminate = (count > 0 && count < allCount);
      checkAll.checked = (allCount > 0 && count === allCount);
    }
  }

  function setAll(v){
    rows().forEach(ch => ch.checked = v);
    updateInfo();
  }

  if (checkAll) {
    checkAll.addEventListener('change', () => setAll(checkAll.checked));
  }

  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('row-check')) {
      updateInfo();
    }
  });

  if (form) {
    form.addEventListener('submit', (e) => {
      const any = rows().some(ch => ch.checked);
      if (!any) {
        e.preventDefault();
        alert('Seleccioná al menos un consumo para crear la rendición.');
        return;
      }
      if (!confirm('¿Crear rendición con los consumos seleccionados?')) {
        e.preventDefault();
      }
    });
  }

  updateInfo();
});
</script>
@endpush
@endsection
