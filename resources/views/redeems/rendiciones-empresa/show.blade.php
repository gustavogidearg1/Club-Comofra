@extends('layouts.app')
@section('title','Rendición a empresa #'.$settlement->id)

@section('content')
<style>
  .chip{
    display:inline-flex; align-items:center; gap:.45rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }
  .chip-ok{
    background:#ECFDF5; color:#047857;
  }
  .small-muted{ color:#6c757d; font-size:.9rem; }
  .table td, .table th { vertical-align: middle; }
  @media print {
    header, nav, footer, .navbar, .sidebar, .offcanvas,
    .pc-sidebar, .pc-header, .pc-footer, .pc-topbar,
    .mat-header-actions, .page-header, .breadcrumb, .alert, .toast, .modal {
      display:none !important;
    }
    .btn { display:none !important; }
    .card { border: none !important; box-shadow: none !important; }
  }
</style>

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header flex-wrap">
      <h3 class="mat-title mb-0 flex-grow-1" style="min-width:240px;">
        <i class="bi bi-receipt-cutoff me-2"></i> Rendición a empresa #{{ $settlement->id }}
      </h3>

      <div class="d-flex gap-2 ms-auto mt-2 mt-lg-0">
        <a href="{{ route('redeems.rendiciones_empresa.index') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-mat">
          <i class="bi bi-printer"></i> Imprimir
        </button>
      </div>

      <div class="d-flex gap-2 ms-auto mt-2 mt-lg-0">

        @if($settlement->status !== 'invoiced' && $settlement->status !== 'cancelled')
  <form method="POST"
        action="{{ route('redeems.rendiciones_empresa.revertir', $settlement->id) }}"
        class="d-inline"
        onsubmit="return confirm('¿Anular rendición y volver consumos a Pendiente?');">
    @csrf
    <button class="btn btn-outline-danger btn-mat">
      <i class="bi bi-arrow-counterclockwise"></i> Volver a pendiente
    </button>
  </form>
@endif


</div>

    </div>

    <div class="card-body p-4">
      <x-flash />

      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <div class="text-muted small">Empresa</div>
          <div class="fw-semibold">{{ $settlement->company?->name ?? '—' }}</div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-muted small">Negocio</div>
          <div class="fw-semibold">{{ $settlement->business?->name ?? '—' }}</div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-muted small">Período</div>
          <div class="fw-semibold">
            {{ $settlement->period_from?->format('d/m/Y') ?? '—' }}
            <span class="text-muted">a</span>
            {{ $settlement->period_to?->format('d/m/Y') ?? '—' }}
          </div>
        </div>

        <div class="col-12 col-md-6 d-flex align-items-start gap-2 flex-wrap">
          <span class="chip">
            <i class="bi bi-123"></i>
            Total: {{ number_format((int)$settlement->total_points, 0, ',', '.') }} pts
          </span>



          @if($settlement->status === 'invoiced')
            <span class="badge text-bg-primary align-self-center">
              Facturada
            </span>
          @else
            <span class="badge text-bg-warning align-self-center">
              Borrador
            </span>
          @endif
        </div>

        @if(!empty($settlement->invoice_number))
          <div class="col-12 col-md-6">
            <div class="text-muted small">Nº de factura</div>
            <div class="fw-semibold">{{ $settlement->invoice_number }}</div>
          </div>
        @endif

        @if(!empty($settlement->note))
          <div class="col-12">
            <div class="text-muted small">Nota</div>
            <div class="fw-semibold">{{ $settlement->note }}</div>
          </div>
        @endif
      </div>

      {{-- Acciones --}}
      @if($settlement->status !== 'invoiced')
        <form method="POST" action="{{ route('redeems.rendiciones_empresa.facturar', $settlement->id) }}" class="row g-2 align-items-end mb-4">
          @csrf
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Nº de factura (opcional)</label>
            <input type="text" name="invoice_number" class="form-control" maxlength="60" placeholder="Ej: A-0001-00001234">
          </div>
          <div class="col-12 col-md-4">
            <button class="btn btn-primary btn-mat">
              <i class="bi bi-check2-circle"></i> Marcar como facturada
            </button>
          </div>
        </form>
      @else
        <div class="alert alert-success mat-alert">
          <i class="bi bi-check2-circle"></i>
          Rendición facturada el {{ $settlement->invoiced_at?->format('d/m/Y H:i') }}.
          @if($settlement->invoicedBy) Por {{ $settlement->invoicedBy->name }}. @endif
        </div>
      @endif

      {{-- Detalle --}}
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>Fecha</th>
              <th>Empleado</th>
              <th class="text-end">Puntos</th>
              <th class="text-end">Importe</th>
              <th>Referencia</th>
              <th>Nota</th>
            </tr>
          </thead>
          <tbody>
            @forelse($settlement->redemptions as $r)
              <tr>
                <td>
                  <div class="fw-semibold">{{ optional($r->confirmed_at)->format('d/m/Y') }}</div>
                  <div class="small-muted">{{ optional($r->confirmed_at)->format('H:i') }}</div>
                </td>
                <td>
                  <div class="fw-semibold">{{ $r->employee?->name ?? '—' }}</div>
                  <div class="small-muted">{{ $r->employee?->cuil ? 'CUIL '.$r->employee->cuil : '' }}</div>
                </td>
                <td class="text-end fw-semibold">
                  {{ number_format((int)$r->points, 0, ',', '.') }}
                </td>
                <td class="text-end">
                  $ {{ number_format((int)$r->points, 0, ',', '.') }}
                </td>
                <td>
                  {{-- Mostrar texto en español (podés mejorar con helper) --}}
                  @php
                    $ref = $r->reference;
                    $refTxt = match($ref) {
                      'GASTO_NEGOCIO' => 'Consumo en negocio',
                      'GASTO_MANUAL_QR' => 'Consumo manual (QR)',
                      default => $ref ?: '—',
                    };
                  @endphp
                  <span class="small-muted">{{ $refTxt }}</span>
                </td>
                <td>{{ $r->note ?? '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="bi bi-inbox"></i> Sin consumos en esta rendición
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr class="table-light">
              <th colspan="2" class="text-end">Totales</th>
              <th class="text-end">{{ number_format((int)$settlement->total_points, 0, ',', '.') }}</th>
              <th class="text-end">$ {{ number_format((int)($settlement->total_amount ?? $settlement->total_points), 0, ',', '.') }}</th>
              <th colspan="2"></th>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>
  </div>
</div>
@endsection
