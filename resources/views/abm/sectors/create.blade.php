@extends('layouts.app')

@section('title', 'Nuevo sector')

@section('content')

<x-flash />

<div class="card mat-card">

    <div class="mat-header d-flex align-items-center">
        <h3 class="mat-title mb-0">
            <i class="bi bi-plus-circle me-2"></i>
            Nuevo sector
        </h3>

        <div class="ms-auto">
            <a href="{{ route('sectors.index') }}"
               class="btn btn-outline-secondary btn-mat">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('sectors.store') }}">
            @csrf

            @include('abm.sectors._form')

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary btn-mat" type="submit">
                    <i class="bi bi-check2"></i>
                    Guardar
                </button>

                <a href="{{ route('sectors.index') }}"
                   class="btn btn-outline-secondary btn-mat">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

@endsection
