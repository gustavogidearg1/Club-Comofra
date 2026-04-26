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

    <form id="form-oferta" action="{{ route('ofertas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('ofertas._form')
    </form>
</div>

<div class="modal fade" id="modalGuardandoOferta" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5 class="mb-2">Guardando oferta</h5>
                <div class="text-muted">
                    Aguarde mientras se guarda la oferta y se programa el envío de correos.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-oferta');
    const modalEl = document.getElementById('modalGuardandoOferta');

    if (form && modalEl && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(modalEl);

        form.addEventListener('submit', function () {
            modal.show();
        });
    }
});
</script>
@endpush
