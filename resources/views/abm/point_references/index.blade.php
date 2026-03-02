@extends('layouts.app')
@section('title','Referencias de puntos')

@section('content')
<x-flash />

<x-page-header title="Referencias de puntos">
  <x-slot:actions>
    <a href="{{ route('abm.point-references.create') }}" class="btn btn-primary btn-mat">
      <i class="bi bi-plus-circle"></i> Nueva
    </a>
  </x-slot:actions>
</x-page-header>

<div class="card mat-card">
  <div class="mat-header d-flex align-items-center">
    <h3 class="mat-title mb-0">
      <i class="bi bi-tags me-2"></i> Listado
    </h3>

    <div class="ms-auto d-flex gap-2">
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}"
               class="form-control" placeholder="Buscar por nombre…">
        <select name="active" class="form-select" style="max-width:170px;">
          <option value="">Todas</option>
          <option value="1" @selected(request('active')==='1')>Activas</option>
          <option value="0" @selected(request('active')==='0')>Inactivas</option>
        </select>
        <button class="btn btn-outline-secondary btn-mat" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:80px;">ID</th>
            <th>Nombre</th>
            <th style="width:180px;">Ámbito</th>
            <th style="width:110px;">Orden</th>
            <th style="width:110px;">Activa</th>
            <th style="width:180px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr>
              <td class="text-muted">#{{ $r->id }}</td>

              <td>
                <div class="fw-semibold">{{ $r->name }}</div>
                <div class="text-muted small">
                  Actualizado: {{ optional($r->updated_at)->format('d/m/Y H:i') }}
                </div>
              </td>

              <td>
                @if($r->company_id)
                  <span class="badge bg-primary">
                    {{ $r->company->name ?? 'Empresa' }}
                  </span>
                @else
                  <span class="badge bg-dark">Global</span>
                @endif
              </td>

              <td>
                <span class="badge bg-light text-dark">
                  {{ $r->sort_order ?? '—' }}
                </span>
              </td>

              <td>
                @if($r->is_active)
                  <span class="badge bg-success">Sí</span>
                @else
                  <span class="badge bg-secondary">No</span>
                @endif
              </td>

              <td class="text-nowrap">
                <a href="{{ route('abm.point-references.show', $r) }}"
                   class="btn btn-sm btn-outline-secondary btn-mat">
                  <i class="bi bi-eye"></i>
                </a>

                <a href="{{ route('abm.point-references.edit', $r) }}"
                   class="btn btn-sm btn-outline-primary btn-mat">
                  <i class="bi bi-pencil"></i>
                </a>

                <form method="POST"
                      action="{{ route('abm.point-references.destroy', $r) }}"
                      class="d-inline"
                      onsubmit="return confirm('¿Eliminar referencia?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger btn-mat" type="submit">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size:1.6rem;"></i>
                <div class="mt-2">No hay referencias.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if(method_exists($rows,'links'))
    <div class="card-body">
      {{ $rows->links() }}
    </div>
  @endif
</div>
@endsection
