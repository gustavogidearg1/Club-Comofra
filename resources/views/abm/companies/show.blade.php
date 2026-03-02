@extends('layouts.app')

@section('title', 'Compañía - '.$company->name)

@section('content')
<x-page-header :title="$company->name">
  <x-slot:actions>
    <a href="{{ route('abm.companies.index') }}" class="btn btn-light btn-mat">
      <i class="bi bi-arrow-left"></i> Volver
    </a>

    <a href="{{ route('abm.companies.edit', $company) }}" class="btn btn-primary btn-mat">
      <i class="bi bi-pencil"></i> Editar
    </a>
  </x-slot:actions>
</x-page-header>

<x-flash />

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mat-card">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          @if($company->logo)
            <img
              src="{{ asset('storage/'.$company->logo) }}"
              alt="Logo {{ $company->name }}"
              style="width:72px;height:72px;object-fit:cover;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,.12);"
            >
          @else
            <div class="company-logo-fallback" style="width:72px;height:72px;border-radius:16px;">
              <i class="bi bi-buildings" style="font-size:1.6rem;"></i>
            </div>
          @endif

          <div>
            <div class="fw-bold" style="font-size:1.05rem;">{{ $company->name }}</div>
            <div class="text-muted small">ID #{{ $company->id }}</div>
          </div>
        </div>

        <hr>

        <div class="small text-muted mb-2">Colores</div>
        <div class="d-flex gap-2">
          <div class="border rounded-3 p-2 flex-fill">
            <div class="text-muted small">Primario</div>
            <div class="fw-semibold">{{ $company->color_primario ?? '—' }}</div>
          </div>
          <div class="border rounded-3 p-2 flex-fill">
            <div class="text-muted small">Secundario</div>
            <div class="fw-semibold">{{ $company->color_secundario ?? '—' }}</div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card mat-card">
      <div class="mat-header">
        <h3 class="mat-title mb-0"><i class="bi bi-info-circle me-2"></i>Datos</h3>
      </div>

      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">CUIT</div>
            <div class="fw-semibold">{{ $company->cuit ?? '—' }}</div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div class="fw-semibold">{{ $company->email ?? '—' }}</div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Teléfono</div>
            <div class="fw-semibold">{{ $company->telefono ?? '—' }}</div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Dirección</div>
            <div class="fw-semibold">{{ $company->direccion ?? '—' }}</div>
          </div>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Creado: {{ optional($company->created_at)->format('d/m/Y H:i') ?? '—' }}
          <span class="mx-2">•</span>
          Actualizado: {{ optional($company->updated_at)->format('d/m/Y H:i') ?? '—' }}
        </div>

        <form action="{{ route('abm.companies.destroy', $company) }}" method="POST"
              onsubmit="return confirm('¿Eliminar compañía?')" class="m-0">
          @csrf @method('DELETE')
          <button class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash"></i> Eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
