@extends('layouts.app')
@section('title','Editar Empresa')

@section('content')
<x-page-header title="Editar Empresa" />

<div class="card mat-card">
  <div class="card-body">
    <form method="POST" action="{{ route('empresas.update',$empresa) }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      @include('abm.empresas._form', ['empresa'=>$empresa])
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary btn-mat">Guardar</button>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">Volver</a>
      </div>
    </form>
  </div>
</div>
@endsection
