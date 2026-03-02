@extends('layouts.app')
@section('title','Editar referencia')

@section('content')
<x-flash />

<div class="card mat-card">
  <div class="mat-header d-flex align-items-center">
    <h3 class="mat-title mb-0">
      <i class="bi bi-pencil me-2"></i> Editar referencia
    </h3>

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('abm.point-references.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>

      <a href="{{ route('abm.point-references.show', $row) }}" class="btn btn-outline-primary btn-mat">
        <i class="bi bi-eye"></i> Ver
      </a>
    </div>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('abm.point-references.update', $row) }}">
      @csrf
      @method('PUT')

      @include('abm.point_references._form', [
        'row' => $row,
        'companies' => $companies ?? collect(),
      ])

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary btn-mat" type="submit">
          <i class="bi bi-check2"></i> Guardar cambios
        </button>

        <a href="{{ route('abm.point-references.index') }}" class="btn btn-outline-secondary btn-mat">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
