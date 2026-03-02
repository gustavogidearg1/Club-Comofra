@extends('layouts.app')

@section('title','Localidades')

@section('content')
<div class="container-fluid py-2">

  {{-- CARD: FORM (CREATE / EDIT) --}}
  <div class="card mat-card mb-3">
    <div class="mat-header d-flex align-items-center">
      <h3 class="mat-title mb-0">
        <i class="bi bi-geo-alt"></i> Localidades
      </h3>

      <div class="ms-auto">
        @if($localidadEdit)
          <a href="{{ route('abm.localidades.index', ['q' => $q]) }}" class="btn btn-light btn-mat btn-sm">
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
            action="{{ $localidadEdit ? route('abm.localidades.update', $localidadEdit) : route('abm.localidades.store') }}"
            class="row g-3 align-items-end">
        @csrf
        @if($localidadEdit) @method('PUT') @endif

        <div class="col-12 col-md-5">
          <label class="form-label">Provincia *</label>
          <select name="provincia_id" class="form-select" required>
            <option value="">— Seleccionar —</option>
            @foreach($provincias as $prov)
              <option value="{{ $prov->id }}"
                @selected((int)old('provincia_id', $localidadEdit->provincia_id ?? 0) === (int)$prov->id)>
                {{ $prov->nombre }} — {{ $prov->pais?->nombre ?? '—' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-5">
          <label class="form-label">Localidad *</label>
          <input type="text"
                 name="nombre"
                 class="form-control"
                 maxlength="120"
                 required
                 value="{{ old('nombre', $localidadEdit->nombre ?? '') }}"
                 placeholder="Ej: Marcos Juárez">
        </div>

        <div class="col-12 col-md-2">
          <label class="form-label">CP</label>
          <input type="text"
                 name="cp"
                 class="form-control"
                 maxlength="12"
                 value="{{ old('cp', $localidadEdit->cp ?? '') }}"
                 placeholder="Ej: 2580">
        </div>

        <div class="col-12 d-grid d-md-flex gap-2 mt-2">
          <button class="btn btn-primary btn-mat" type="submit">
            <i class="bi {{ $localidadEdit ? 'bi-check2' : 'bi-plus-lg' }} me-1"></i>
            {{ $localidadEdit ? 'Guardar cambios' : 'Agregar' }}
          </button>

          @if(!$localidadEdit)
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
        <form method="GET" action="{{ route('abm.localidades.index') }}" class="d-flex">
          <div class="input-group input-group-sm">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>

            <input type="text"
                   name="q"
                   class="form-control"
                   placeholder="Buscar localidad, CP, provincia o país..."
                   value="{{ $q ?? '' }}">

            <button class="btn btn-outline-secondary" type="submit" title="Buscar">
              <i class="bi bi-search"></i>
            </button>

            @if(!empty($q))
              <a class="btn btn-outline-secondary"
                 href="{{ route('abm.localidades.index') }}"
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
              <th>Localidad</th>
              <th class="text-center">CP</th>
              <th>Provincia</th>
              <th>País</th>
              <th class="text-end" style="width:190px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            @forelse($localidades as $localidad)
              <tr>
                <td class="fw-semibold">{{ $localidad->nombre }}</td>
                <td class="text-center">{{ $localidad->cp ?? '—' }}</td>
                <td>{{ $localidad->provincia?->nombre ?? '—' }}</td>
                <td>{{ $localidad->provincia?->pais?->nombre ?? '—' }}</td>

                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary btn-mat"
                     href="{{ route('abm.localidades.index', ['edit' => $localidad->id, 'q' => $q]) }}">
                    <i class="bi bi-pencil me-1"></i> Editar
                  </a>

                  <form action="{{ route('abm.localidades.destroy', $localidad) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('¿Eliminar localidad {{ $localidad->nombre }}?');">
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
                <td colspan="5" class="text-center text-muted py-4">
                  Sin registros
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $localidades->links() }}
      </div>

    </div>
  </div>

</div>
@endsection
