@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nuevo sector</h1>

    <form method="POST" action="{{ route('sectors.store') }}">
        @include('abm.sectors._form')
    </form>
</div>
@endsection
