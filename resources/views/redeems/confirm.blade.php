@extends('layouts.app')
@section('title','Comprobante de consumo')

@push('styles')
<style>
  .receipt-page{
    padding:1rem;
  }

  .mat-card{
    border:0;
    border-radius:22px;
    box-shadow:0 10px 28px rgba(15,23,42,.12);
    overflow:hidden;
  }

  .mat-header{
    padding:1rem 1rem .75rem;
    border-bottom:1px solid rgba(15,23,42,.08);
    background:#fff;
  }

  .mat-title{
    font-weight:800;
    font-size:1.05rem;
    color:#0f172a;
    margin:0;
    line-height:1.2;
  }

  .business-hero{
    background:#f8fafc;
    border:1px solid rgba(15,23,42,.08);
    border-radius:18px;
    padding:1rem;
    margin-bottom:1rem;
  }

  .business-label,
  .receipt-label{
    font-size:.76rem;
    font-weight:800;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.08rem;
    margin-bottom:.25rem;
  }

  .business-name{
    font-size:1.35rem;
    font-weight:900;
    color:#0f172a;
    line-height:1.2;
  }

  .points-hero{
    background:linear-gradient(135deg,#11836f,#159b77);
    color:#fff;
    border-radius:22px;
    padding:1.35rem 1rem;
    text-align:center;
    margin-bottom:1.25rem;
    box-shadow:0 12px 28px rgba(17,131,111,.25);
  }

  .points-hero__label{
    font-size:.74rem;
    font-weight:800;
    letter-spacing:.13rem;
    text-transform:uppercase;
    opacity:.9;
  }

  .points-hero__value{
    font-size:clamp(2.25rem, 11vw, 3.25rem);
    font-weight:900;
    line-height:1;
    margin:.55rem 0 .45rem;
    word-break:break-word;
  }

  .points-hero__subtitle{
    font-size:.9rem;
    font-weight:700;
    opacity:.95;
  }

  .receipt-status{
    margin-top:.9rem;
  }

  .receipt-status .badge{
    border-radius:999px;
    padding:.45rem .8rem;
    font-size:.75rem;
  }

  .receipt-info{
    display:grid;
    gap:.75rem;
  }

  .receipt-item{
    padding:.8rem 0;
    border-bottom:1px solid rgba(15,23,42,.08);
  }

  .receipt-item:last-child{
    border-bottom:0;
  }

  .receipt-value{
    font-size:1rem;
    color:#0f172a;
    font-weight:600;
  }

  .receipt-actions{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.75rem;
    margin-top:1.35rem;
    padding-top:1rem;
    border-top:1px solid rgba(15,23,42,.08);
  }

  .btn-mat{
    border-radius:999px;
    padding:.75rem 1rem;
    font-weight:800;
    box-shadow:0 4px 10px rgba(15,23,42,.10);
  }

  @media (max-width: 576px){
    .receipt-page{
      padding:.75rem;
    }

    .business-name{
      font-size:1.2rem;
    }

    .points-hero{
      padding:1.25rem .85rem;
    }

    .points-hero__value{
      font-size:clamp(2rem, 10vw, 2.8rem);
    }
  }
</style>
@endpush

@section('content')

@php
  $empleado = $redemption->employee->name ?? '—';
  $puntos   = number_format((float)($redemption->points ?? 0), 2, ',', '.');
  $negocio  = $redemption->business->name ?? '—';
  $fecha    = optional($redemption->confirmed_at)->format('Y-m-d H:i') ?? '—';
  $nota     = $redemption->note ?? '—';

  $estado = (($redemption->status ?? '') === 'voided' || optional($redemption->movement)->voided_at)
    ? 'ANULADO'
    : 'CONFIRMADO';

  $shareText =
"🧾 Comprobante de consumo
Negocio: {$negocio}
Puntos consumidos: {$puntos}
Empleado: {$empleado}
Fecha: {$fecha}
Nota: {$nota}
Estado: {$estado}";

  $waLink = 'https://wa.me/?text=' . rawurlencode($shareText);
@endphp

<div class="container receipt-page">
  <div class="card mat-card">

    <div class="mat-header">
      <h3 class="mat-title">
        <i class="bi bi-receipt me-2"></i>
        Comprobante de consumo
      </h3>
    </div>

    <div class="card-body p-3 p-sm-4">

      <div class="business-hero">
        <div class="business-label">Negocio</div>
        <div class="business-name">{{ $negocio }}</div>
      </div>

      <div class="points-hero">
        <div class="points-hero__label">
          Puntos consumidos
        </div>

        <div class="points-hero__value">
          {{ $puntos }}
        </div>

        <div class="points-hero__subtitle">
          PUNTOS
        </div>

        <div class="receipt-status">
          @if($estado === 'ANULADO')
            <span class="badge bg-danger">
              <i class="bi bi-x-circle me-1"></i> ANULADO
            </span>
          @else
            <span class="badge bg-light text-success">
              <i class="bi bi-check-circle me-1"></i> CONFIRMADO
            </span>
          @endif
        </div>
      </div>

      <div class="receipt-info">

        <div class="receipt-item">
          <div class="receipt-label">Empleado</div>
          <div class="receipt-value">{{ $empleado }}</div>
        </div>

        <div class="receipt-item">
          <div class="receipt-label">Fecha</div>
          <div class="receipt-value">{{ $fecha }}</div>
        </div>

        <div class="receipt-item">
          <div class="receipt-label">Nota</div>
          <div class="receipt-value">{{ $nota }}</div>
        </div>

      </div>

      <div class="receipt-actions">
        <a href="{{ $waLink }}"
           target="_blank"
           rel="noopener"
           class="btn btn-success btn-mat">
          <i class="bi bi-whatsapp me-1"></i>
          Compartir
        </a>

        <a href="{{ route('points.index') }}"
           class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left me-1"></i>
          Volver
        </a>
      </div>

    </div>
  </div>
</div>

<audio id="clubSuccessSound" preload="auto">
  <source src="{{ asset('audio/audio-success.wav') }}" type="audio/wav">
</audio>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sound = document.getElementById('clubSuccessSound');

    if (!sound) return;

    sound.volume = 0.75;

    sound.play().catch(function () {
        console.log('El navegador bloqueó la reproducción automática del sonido.');
    });
});
</script>

@endsection
