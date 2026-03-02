@extends('layouts.app')
@section('title','Editar Compañía')

@section('content')
<x-page-header title="Editar Compañía" />

<div class="card mat-card">
  <div class="card-body">
    <form method="POST" action="{{ route('companies.update',$company) }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      @include('abm.companies._form', ['company'=>$company])
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary btn-mat">Guardar</button>
        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Volver</a>
      </div>
    </form>
  </div>
</div>
@endsection
