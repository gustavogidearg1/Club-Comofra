@extends('layouts.app')
@section('title','Nueva Compañía')

@section('content')
<x-page-header title="Nueva Compañía" />

<div class="card mat-card">
  <div class="card-body">
    <form method="POST" action="{{ route('abm.companies.store') }}" enctype="multipart/form-data">
      @csrf
      @include('abm.companies._form')
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary btn-mat">Guardar</button>
        <a href="{{ route('abm.companies.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
