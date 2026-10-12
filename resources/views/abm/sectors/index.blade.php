@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Sectores</h1>

        <a href="{{ route('abm.sectors.create') }}" class="btn btn-primary">
            Nuevo sector
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse($sectors as $sector)
                <tr>
                    <td>{{ $sector->name }}</td>

                    <td>
                        @if($sector->active)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>

                    <td>
                        <a
                            href="{{ route('abm.sectors.edit', $sector) }}"
                            class="btn btn-sm btn-warning"
                        >
                            Editar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">
                        No hay sectores cargados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $sectors->links() }}
</div>
@endsection
