@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mat-card shadow-sm border-0">
        <div class="card-header mat-header bg-white d-flex align-items-center">
            <h3 class="mat-title mb-0">
                <i class="fas fa-tag me-2"></i> Detalle de oferta
            </h3>

            <div class="ms-auto d-flex gap-2">

                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    Volver
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-7">
                    <h2 class="mb-3">{{ $oferta->titulo }}</h2>

                    @if($oferta->descripcion_corta)
                        <p class="text-muted">{{ $oferta->descripcion_corta }}</p>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <strong>Empresa:</strong><br>
                            {{ $oferta->company->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Creada por:</strong><br>
                            {{ $oferta->user->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Precio:</strong><br>
                            {{ $oferta->precio ? '$ ' . number_format($oferta->precio, 2, ',', '.') : '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Precio anterior:</strong><br>
                            {{ $oferta->precio_anterior ? '$ ' . number_format($oferta->precio_anterior, 2, ',', '.') : '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha desde:</strong><br>
                            {{ $oferta->fecha_desde?->format('d/m/Y') ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha hasta:</strong><br>
                            {{ $oferta->fecha_hasta?->format('d/m/Y') ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-secondary">{{ ucfirst($oferta->estado) }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Publicada:</strong><br>
                            {!! $oferta->publicada ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-warning text-dark">No</span>' !!}
                        </div>
                        <div class="col-md-4">
                            <strong>Destacada:</strong><br>
                            {!! $oferta->destacada ? '<span class="badge bg-primary">Sí</span>' : '-' !!}
                        </div>
                    </div>

                    @if($oferta->descripcion)
                        <div class="mb-3">
                            <strong>Descripción</strong>
                            <p class="mb-0">{{ $oferta->descripcion }}</p>
                        </div>
                    @endif

                    @if($oferta->observaciones)
                        <div class="mb-0">
                            <strong>Observaciones</strong>
                            <p class="mb-0">{{ $oferta->observaciones }}</p>
                        </div>
                    @endif
                </div>

                <div class="col-md-5">
                    @if($oferta->imagenes->count())
                        <div id="carouselOferta{{ $oferta->id }}" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded overflow-hidden">
                                @foreach($oferta->imagenes as $index => $imagen)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                             class="d-block w-100"
                                             style="height: 320px; object-fit: cover;"
                                             alt="Imagen de oferta">
                                    </div>
                                @endforeach
                            </div>

                            @if($oferta->imagenes->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselOferta{{ $oferta->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselOferta{{ $oferta->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="border rounded p-4 text-center text-muted">
                            Sin imágenes cargadas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
