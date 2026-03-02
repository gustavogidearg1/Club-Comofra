@extends('layouts.app')

@section('title','Provincias')

@section('content')
<div class="container-fluid py-2">

  {{-- CARD: FORM (CREATE / EDIT) --}}
  <div class="card mat-card mb-3">
    <div class="mat-header d-flex align-items-center">
      <h3 class="mat-title mb-0">
        <i class="bi bi-map"></i> Provincias
      </h3>

      <div class="ms-auto">
        @if($provinciaEdit)
          <a href="{{ route('abm.provincias.index', ['q' => $q]) }}" class="btn btn-light btn-mat btn-sm">
            <i class="bi bi-x-lg me-1"></i> Cancelar edición
          </a>
        @endif
      </div>
    </div>

    <div class="card-body">

      @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger mb-3">
          <div class="fw-semibold mb-1">Revisá los campos:</div>
          <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form method="POST"
            action="{{ $provinciaEdit ? route('abm.provincias.update', $provinciaEdit) : route('abm.provincias.store') }}"
            class="row g-3 align-items-end">
        @csrf
        @if($provinciaEdit) @method('PUT') @endif

        <div class="col-12 col-md-5">
          <label class="form-label">País *</label>
          <select name="pais_id" class="form-select" required>
            <option value="">— Seleccionar —</option>
            @foreach($paises as $pais)
              <option value="{{ $pais->id }}"
                @selected((int)old('pais_id', $provinciaEdit->pais_id ?? 0) === (int)$pais->id)>
                {{ $pais->nombre }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-7">
          <label class="form-label">Provincia *</label>
          <input type="text"
                 name="nombre"
                 class="form-control"
                 maxlength="100"
                 required
                 value="{{ old('nombre', $provinciaEdit->nombre ?? '') }}"
                 placeholder="Ej: Córdoba">
        </div>

        <div class="col-12 d-grid d-md-flex gap-2 mt-2">
          <button class="btn btn-primary btn-mat" type="submit">
            <i class="bi {{ $provinciaEdit ? 'bi-check2' : 'bi-plus-lg' }} me-1"></i>
            {{ $provinciaEdit ? 'Guardar cambios' : 'Agregar' }}
          </button>

          @if(!$provinciaEdit)
            <button type="reset" class="btn btn-outline-secondary btn-mat">
              Limpiar
            </button>
          @endif
        </div>
      </form>

    </div>
  </div>

  {{-- CARD: TABLA --}}
  <div class="card mat-card">
    <div class="mat-header d-flex align-items-center">
      <h3 class="mat-title mb-0">
        <i class="bi bi-list-ul"></i> Listado
      </h3>

      <div class="ms-auto d-flex gap-2">
<form method="GET" action="{{ route('abm.provincias.index') }}" class="d-flex">
  <div class="input-group input-group-sm">

    <input type="text"
           name="q"
           class="form-control"
           placeholder="Buscar provincia o país..."
           value="{{ $q ?? '' }}">

    <button class="btn btn-outline-secondary" type="submit" title="Buscar">
      <i class="bi bi-search"></i>
    </button>

    @if(!empty($q))
      <a class="btn btn-outline-secondary"
         href="{{ route('abm.provincias.index') }}"
         title="Limpiar filtro">
        <i class="bi bi-x-lg"></i>
      </a>
    @endif

  </div>
</form>


      </div>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:90px;">ID</th>
              <th>Provincia</th>
              <th>País</th>
              <th class="text-end" style="width:190px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            @forelse($provincias as $provincia)
              <tr>
                <td class="text-muted">{{ $provincia->id }}</td>
                <td class="fw-semibold">{{ $provincia->nombre }}</td>
                <td>{{ $provincia->pais?->nombre ?? '—' }}</td>

                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary btn-mat"
                     href="{{ route('abm.provincias.index', ['edit' => $provincia->id, 'q' => $q]) }}">
                    <i class="bi bi-pencil me-1"></i> Editar
                  </a>

                  <form action="{{ route('abm.provincias.destroy', $provincia) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('¿Eliminar provincia {{ $provincia->nombre }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-mat">
                      <i class="bi bi-trash me-1"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  Sin registros
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $provincias->links() }}
      </div>

    </div>
  </div>

</div>
@endsection
