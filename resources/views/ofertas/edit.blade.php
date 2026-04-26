@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisá los datos cargados.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ofertas.update', $oferta) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('ofertas._form', ['oferta' => $oferta])
    </form>
</div>
@endsection
