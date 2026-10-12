@extends('layouts.app')

@section('title', 'Sectores')

@section('content')

<x-flash />

<x-page-header title="Sectores">
    <x-slot:actions>
        <a href="{{ route('sectors.create') }}" class="btn btn-primary btn-mat">
            <i class="bi bi-plus-circle"></i>
            Nuevo sector
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card mat-card">

    <div class="mat-header d-flex align-items-center">
        <h3 class="mat-title mb-0">
            <i class="bi bi-diagram-3 me-2"></i>
            Listado
        </h3>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">

            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Sector</th>
                        <th>Código</th>
                        <th>Control de calidad</th>
                        <th>Estado</th>
                        <th style="width: 150px;">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sectors as $sector)
                        <tr>
                            <td class="text-muted">
                                #{{ $sector->id }}
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $sector->name }}
                                </div>

                                <div class="text-muted small">
                                    Actualizado:
                                    {{ optional($sector->updated_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $sector->code ?: '—' }}
                                </span>
                            </td>

                            <td>
                                @if($sector->calidad)
                                    <span class="badge bg-success">
                                        Sí
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        No
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($sector->active)
                                    <span class="badge bg-success">
                                        Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <a
                                    href="{{ route('sectors.edit', $sector) }}"
                                    class="btn btn-sm btn-outline-primary btn-mat"
                                    title="Editar"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 1.6rem;"></i>

                                <div class="mt-2">
                                    No hay sectores cargados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

    @if(method_exists($sectors, 'links'))
        <div class="card-body">
            {{ $sectors->links() }}
        </div>
    @endif

</div>

@endsection
