@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="card mat-card shadow-sm border-0">
            <div class="card-header mat-header bg-white d-flex align-items-center">
                <h3 class="mat-title mb-0">
                    <i class="fas fa-tags me-2"></i> Ofertas
                </h3>

                <div class="ms-auto">
                    <a href="{{ route('ofertas.create') }}" class="btn btn-primary btn-mat">
                        <i class="fas fa-plus me-1"></i> Nueva oferta
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Empresa</th>
                                <th>Precio</th>
                                <th>Vigencia</th>
                                <th>Estado</th>
                                <th>Publicada</th>
                                <th>Destacada</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ofertas as $oferta)
                                <tr>
                                    <td>{{ $oferta->id }}</td>
                                    <td>
                                        @php
                                            $img =
                                                $oferta->imagenes->firstWhere('principal', true) ??
                                                $oferta->imagenes->first();
                                        @endphp

                                        @if ($img)
                                            <img src="{{ asset('storage/' . $img->ruta) }}" alt="Imagen"
                                                style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;">
                                        @else
                                            <span class="text-muted">Sin imagen</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $oferta->titulo }}</strong>
                                        @if ($oferta->descripcion_corta)
                                            <br><small class="text-muted">{{ $oferta->descripcion_corta }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $oferta->company->name ?? '-' }}</td>
                                    <td>{{ $oferta->precio ? '$ ' . number_format($oferta->precio, 2, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        {{ $oferta->fecha_desde?->format('d/m/Y') ?? '-' }}
                                        <br>
                                        {{ $oferta->fecha_hasta?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($oferta->estado) }}</span>
                                    </td>
                                    <td>
                                        {!! $oferta->publicada
                                            ? '<span class="badge bg-success">Sí</span>'
                                            : '<span class="badge bg-warning text-dark">No</span>' !!}
                                    </td>
                                    <td>
                                        {!! $oferta->destacada ? '<span class="badge bg-primary">Sí</span>' : '-' !!}
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group" role="group">

                                            <a href="{{ route('ofertas.show', $oferta) }}"
                                                class="btn btn-outline-secondary btn-sm btn-mat">
                                                Ver
                                            </a>

                                            <a href="{{ route('ofertas.edit', $oferta) }}"
                                                class="btn btn-outline-primary btn-sm btn-mat">
                                                Editar
                                            </a>

                                            <form action="{{ route('ofertas.destroy', $oferta) }}" method="POST"
                                                onsubmit="return confirm('¿Seguro que querés eliminar esta oferta?')"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-mat">
                                                    Eliminar
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay ofertas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $ofertas->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
