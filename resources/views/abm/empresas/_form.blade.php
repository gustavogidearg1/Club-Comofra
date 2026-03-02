<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nombre</label>
    <input name="name" class="form-control mat-input @error('name') is-invalid @enderror"
           value="{{ old('name', $empresa->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label">Nivel</label>
    <input type="number" name="nivel" class="form-control mat-input @error('nivel') is-invalid @enderror"
           value="{{ old('nivel', $empresa->nivel ?? 1) }}" min="1" required>
    @error('nivel')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label">Contacto</label>
    <input name="contacto" class="form-control mat-input"
           value="{{ old('contacto', $empresa->contacto ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">CUIT</label>
    <input name="cuit" class="form-control mat-input" value="{{ old('cuit', $empresa->cuit ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control mat-input"
           value="{{ old('email', $empresa->email ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Teléfono</label>
    <input name="telefono" class="form-control mat-input"
           value="{{ old('telefono', $empresa->telefono ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Dirección</label>
    <input name="direccion" class="form-control mat-input"
           value="{{ old('direccion', $empresa->direccion ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Logo</label>
    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if(!empty($empresa?->logo))
      <div class="small text-muted mt-1">Actual: {{ $empresa->logo }}</div>
    @endif
  </div>

  <div class="col-12">
    <label class="form-label">Observación</label>
    <textarea name="observacion" class="form-control mat-input" rows="3">{{ old('observacion', $empresa->observacion ?? '') }}</textarea>
  </div>
</div>
