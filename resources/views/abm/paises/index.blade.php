@extends('layouts.app')

@section('title','Paises')

@section('content')
<div class="container-fluid py-2">

  {{-- CARD: FORM (CREATE / EDIT) --}}
  <div class="card mat-card mb-3">
    <div class="mat-header d-flex align-items-center">
      <h3 class="mat-title mb-0">
        <i class="bi bi-globe"></i> Paises
      </h3>

      <div class="ms-auto">
        @if($paisEdit)
          <a href="{{ route('abm.paises.index', ['q' => $q]) }}" class="btn btn-light btn-mat btn-sm">
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

      {{-- FORM: create o edit --}}
      <form method="POST"
            action="{{ $paisEdit ? route('abm.paises.update', $paisEdit) : route('abm.paises.store') }}"
            class="row g-3 align-items-end">
        @csrf
        @if($paisEdit) @method('PUT') @endif

        <div class="col-12 col-md-6">
          <label class="form-label">Nombre *</label>
          <input type="text"
                 name="nombre"
                 class="form-control"
                 maxlength="80"
                 required
                 value="{{ old('nombre', $paisEdit->nombre ?? '') }}"
                 placeholder="Ej: Argentina">
        </div>



        <div class="col-12 d-grid d-md-flex gap-2 mt-2">
          <button class="btn btn-primary btn-mat" type="submit">
            <i class="bi {{ $paisEdit ? 'bi-check2' : 'bi-plus-lg' }} me-1"></i>
            {{ $paisEdit ? 'Guardar cambios' : 'Agregar' }}
          </button>

          @if(!$paisEdit)
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
        <form class="d-flex" method="GET" action="{{ route('abm.paises.index') }}">
          <input type="text"
                 name="q"
                 class="form-control form-control-sm"
                 placeholder="Buscar por nombre"
                 value="{{ $q ?? '' }}">
        </form>
      </div>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:90px;">ID</th>
              <th>Nombre</th>
              <th class="text-end" style="width:190px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            @forelse($paises as $pais)
              <tr>
                <td class="text-muted">{{ $pais->id }}</td>
                <td class="fw-semibold">{{ $pais->nombre }}</td>


                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary btn-mat"
                     href="{{ route('abm.paises.index', ['edit' => $pais->id, 'q' => $q]) }}">
                    <i class="bi bi-pencil me-1"></i> Editar
                  </a>

                  <form action="{{ route('abm.paises.destroy', $pais) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('¿Eliminar país {{ $pais->nombre }}?');">
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
        {{ $paises->links() }}
      </div>

    </div>
  </div>

</div>
@endsection
