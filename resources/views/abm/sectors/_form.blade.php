@csrf

<div class="mb-3">
    <label class="form-label">Nombre</label>

    <input type="text"
           name="name"
           class="form-control"
           value="{{ old('name', $sector->name ?? '') }}"
           required>
</div>

<div class="mb-3">
    <label class="form-label">Código</label>

    <input type="text"
           name="code"
           class="form-control"
           value="{{ old('code', $sector->code ?? '') }}">
</div>

<div class="form-check mb-3">
    <input type="hidden" name="calidad" value="0">

    <input type="checkbox"
           name="calidad"
           value="1"
           class="form-check-input"
           @checked(old('calidad', $sector->calidad ?? false))>

    <label class="form-check-label">
        Controla calidad
    </label>
</div>

@if(isset($sector))
    <div class="form-check mb-3">
        <input type="hidden" name="active" value="0">

        <input type="checkbox"
               name="active"
               value="1"
               class="form-check-input"
               @checked(old('active', $sector->active))>

        <label class="form-check-label">
            Activo
        </label>
    </div>
@endif

<button class="btn btn-warning">
    Guardar
</button>
