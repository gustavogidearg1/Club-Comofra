@csrf

<div class="row g-3">

    <div class="col-md-8">
        <label for="name" class="form-label">
            Nombre del sector
        </label>

        <input
            type="text"
            name="name"
            id="name"
            class="form-control"
            value="{{ old('name', $sector->name ?? '') }}"
            required
        >

        @error('name')
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="code" class="form-label">
            Código
        </label>

        <input
            type="text"
            name="code"
            id="code"
            class="form-control"
            value="{{ old('code', $sector->code ?? '') }}"
        >

        @error('code')
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>

</div>

<div class="border-top mt-4 pt-4">

    <div class="form-check mb-3">
        <input type="hidden" name="calidad" value="0">

        <input
            type="checkbox"
            name="calidad"
            id="calidad"
            value="1"
            class="form-check-input"
            @checked(old('calidad', $sector->calidad ?? false))
        >

        <label for="calidad" class="form-check-label">
            Controla calidad
        </label>
    </div>

    @if(isset($sector))
        <div class="form-check">
            <input type="hidden" name="active" value="0">

            <input
                type="checkbox"
                name="active"
                id="active"
                value="1"
                class="form-check-input"
                @checked(old('active', $sector->active))
            >

            <label for="active" class="form-check-label">
                Sector activo
            </label>
        </div>
    @else
        <input type="hidden" name="active" value="1">
    @endif

</div>
