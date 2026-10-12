@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar sector</h1>

    <form method="POST" action="{{ route('sectors.update', $sector) }}">
        @method('PUT')

        @include('abm.sectors._form')
    </form>
</div>
@endsection
