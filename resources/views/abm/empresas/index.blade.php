@extends('layouts.app')
@section('title','Empresas')

@section('content')
<x-page-header title="Empresas">
  <x-slot:actions>
    <a href="{{ route('empresas.create') }}" class="btn btn-primary btn-mat">
      <i class="bi bi-plus-circle"></i> Nuevo
    </a>
  </x-slot:actions>
</x-page-header>

<x-flash />

<div class="card mat-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Nivel</th>
          <th>Contacto</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td class="fw-semibold">{{ $r->name }}</td>
            <td>{{ $r->nivel }}</td>
            <td>{{ $r->contacto ?? '-' }}</td>
            <td class="text-end">
              <a href="{{ route('empresas.edit',$r) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
              <form action="{{ route('empresas.destroy',$r) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('¿Eliminar empresa?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center p-4 text-muted">Sin empresas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $rows->links() }}</div>
</div>
@endsection
