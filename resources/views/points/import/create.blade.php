@extends('layouts.app')

@section('title','Carga Masiva de Puntos')

@section('content')
<x-page-header title="Carga Masiva de Puntos" />

<x-flash />

<div class="card mat-card">
  <div class="card-body">
    <h5 class="mb-2">Subir CSV</h5>
    <p class="text-muted small mb-3">
      Columnas requeridas: <code>cuil_empleado</code>, <code>puntos</code>, <code>fecha</code>.
      Opcionales: <code>referencia</code>, <code>nota</code>.
    </p>

 <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <a class="btn btn-outline-secondary btn-mat"
     href="{{ asset('Mas/EjemploImportacionClubComofraCVS.csv') }}"
     download>
    <i class="bi bi-download me-1"></i> Descargar ejemplo (CSV)
  </a>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <a class="btn btn-outline-secondary btn-mat"
       href="{{ asset('Mas/EjemploImportacionClubComofraExcel.xlsx') }}"
       download>
      <i class="bi bi-file-earmark-excel me-1"></i> Descargar ejemplo (Excel)
    </a>

    <span class="text-muted small">
      (Abrir y <strong>Guardar como CSV</strong> usando separador <code>;</code>)
    </span>
  </div>
</div>


    <form method="POST" action="{{ route('points.import.preview') }}" enctype="multipart/form-data">
      @csrf

      <div class="mb-3">
        <label class="form-label">Archivo CSV</label>
        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <button class="btn btn-primary btn-mat">
        <i class="bi bi-eye me-1"></i> Previsualizar
      </button>
    </form>
  </div>
</div>
@endsection
