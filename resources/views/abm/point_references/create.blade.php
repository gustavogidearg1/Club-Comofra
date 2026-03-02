@extends('layouts.app')
@section('title','Nueva referencia')

@section('content')
<x-flash />

<div class="card mat-card">
  <div class="mat-header d-flex align-items-center">
    <h3 class="mat-title mb-0">
      <i class="bi bi-plus-circle me-2"></i> Nueva referencia
    </h3>

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('abm.point-references.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('abm.point-references.store') }}">
      @csrf

      @include('abm.point_references._form', [
        'row' => $row,
        'companies' => $companies ?? collect(),
      ])

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary btn-mat" type="submit">
          <i class="bi bi-check2"></i> Guardar
        </button>

        <a href="{{ route('abm.point-references.index') }}" class="btn btn-outline-secondary btn-mat">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
