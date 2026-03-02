{{-- resources/views/points/empleado-view.blade.php --}}
@extends('layouts.app')

@section('title', 'Mis Puntos')

@section('content')
<x-flash />

<style>
  .points-positive { color: #198754; font-weight: 700; }
  .points-negative { color: #dc3545; font-weight: 700; }
  .points-neutral  { color: #6c757d; font-weight: 700; }

  .summary-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
  }
  .stats-number { font-size: 1.8rem; font-weight: 800; margin-bottom: .1rem; }
  .stats-label  { font-size: .9rem; color: #6c757d; }

  .type-badge { font-size: .75rem; padding: .2rem .55rem; border-radius: 50rem; }
  .badge-earn   { background:#d1e7dd; color:#0f5132; }
  .badge-redeem { background:#f8d7da; color:#842029; }
  .badge-adjust { background:#cff4fc; color:#055160; }
  .badge-expire { background:#e2e3e5; color:#41464b; }

  .badge-voided { background:#fde2e2; color:#842029; }
</style>

<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-wallet2"></i> Mis Puntos
    </h3>
  </div>

  <div class="card-body">

    {{-- Resumen --}}
    <div class="row g-3 mb-4">

      <div class="col-12 col-md-4">
        <div class="card summary-card h-100 border-primary">
          <div class="card-body text-center">
            <div class="text-primary">
              <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
            </div>
            <div class="stats-number">{{ number_format($totals['available'] ?? 0) }}</div>
            <div class="stats-label">Puntos disponibles</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card summary-card h-100 border-success">
          <div class="card-body text-center">
            <div class="text-success">
              <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
            </div>
            <div class="stats-number">{{ number_format($totals['total_earned'] ?? 0) }}</div>
            <div class="stats-label">Puntos ganados</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card summary-card h-100 border-danger">
          <div class="card-body text-center">
            <div class="text-danger">
              <i class="bi bi-arrow-down-circle" style="font-size: 2rem;"></i>
            </div>
            <div class="stats-number">{{ number_format($totals['total_redeemed'] ?? 0) }}</div>
            <div class="stats-label">Puntos canjeados</div>
          </div>
        </div>
      </div>

    </div>

    {{-- Historial --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="mb-0">
        <i class="bi bi-clock-history"></i> Historial de movimientos
      </h5>
      <small class="text-muted">
        Mostrando {{ $points->firstItem() ?? 0 }}–{{ $points->lastItem() ?? 0 }} de {{ $points->total() }}
      </small>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th class="text-end">Puntos</th>
            <th>Negocio</th>
            <th>Referencia</th>
            <th>Detalle</th>
          </tr>
        </thead>

        <tbody>
          @forelse($points as $p)
            @php
              $typeClass = [
                'earn'   => 'badge-earn',
                'redeem' => 'badge-redeem',
                'adjust' => 'badge-adjust',
                'expire' => 'badge-expire',
              ][$p->type] ?? 'badge-expire';

              // ✅ Traducción desde config/points.php
              $typeText = config("points.types.{$p->type}") ?? ucfirst($p->type);

              $pts = (int)($p->points ?? 0);

              // Si existe la columna (después de tu migración), mostramos anulado
              $isVoided = isset($p->voided_at) && !is_null($p->voided_at);
            @endphp

            <tr>
              <td style="white-space:nowrap;">
                <div class="fw-semibold">{{ optional($p->occurred_at)->format('d/m/Y') }}</div>
                <small class="text-muted">{{ optional($p->occurred_at)->format('H:i') }}</small>

                @if($isVoided)
                  <div class="mt-1">
                    <span class="type-badge badge-voided">ANULADO</span>
                  </div>
                @endif
              </td>

              <td>
                <span class="type-badge {{ $typeClass }}">{{ $typeText }}</span>
              </td>

              <td class="text-end">
                <span class="{{ $pts >= 0 ? 'points-positive' : 'points-negative' }}">
                  {{ $pts >= 0 ? '+' : '-' }}{{ number_format(abs($pts)) }}
                </span>
              </td>

              {{-- ✅ Negocio que consumió --}}
              <td>
                @if($p->type === 'redeem')
                  @if(!empty($p->business?->name))
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-shop me-1"></i>{{ $p->business->name }}
                    </span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                @if(!empty($p->reference))
                  <span class="badge bg-light text-dark">{{ $p->reference }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                @if(!empty($p->note))
                  <span title="{{ $p->note }}">{{ \Illuminate\Support\Str::limit($p->note, 60) }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <div class="mt-2">Todavía no tenés movimientos de puntos.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3 d-flex justify-content-end">
      {{ $points->withQueryString()->links() }}
    </div>

  </div>
</div>
@endsection
