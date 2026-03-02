@extends('layouts.app')
@section('title','Detalle referencia')

@section('content')
<x-flash />

<div class="card mat-card">
  <div class="mat-header d-flex align-items-center">
    <h3 class="mat-title mb-0">
      <i class="bi bi-tag me-2"></i> Referencia #{{ $row->id }}
    </h3>

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('abm.point-references.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>

      <a href="{{ route('abm.point-references.edit', $row) }}" class="btn btn-primary btn-mat">
        <i class="bi bi-pencil"></i> Editar
      </a>
    </div>
  </div>

  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-4">Nombre</dt>
      <dd class="col-sm-8 fw-semibold">{{ $row->name }}</dd>

      <dt class="col-sm-4">Ámbito</dt>
      <dd class="col-sm-8">
        @if($row->company_id)
          <span class="badge bg-primary">{{ $row->company->name ?? 'Empresa' }}</span>
        @else
          <span class="badge bg-dark">Global</span>
        @endif
      </dd>

      <dt class="col-sm-4">Orden</dt>
      <dd class="col-sm-8">
        <span class="badge bg-light text-dark">{{ $row->sort_order ?? '—' }}</span>
      </dd>

      <dt class="col-sm-4">Activa</dt>
      <dd class="col-sm-8">
        @if($row->is_active)
          <span class="badge bg-success">Sí</span>
        @else
          <span class="badge bg-secondary">No</span>
        @endif
      </dd>

      <dt class="col-sm-4">Creada</dt>
      <dd class="col-sm-8">{{ optional($row->created_at)->format('d/m/Y H:i') }}</dd>

      <dt class="col-sm-4">Actualizada</dt>
      <dd class="col-sm-8">{{ optional($row->updated_at)->format('d/m/Y H:i') }}</dd>
    </dl>

    <div class="d-flex gap-2 mt-4">
      <form method="POST"
            action="{{ route('abm.point-references.destroy', $row) }}"
            onsubmit="return confirm('¿Eliminar referencia?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger btn-mat" type="submit">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
