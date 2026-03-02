<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nombre</label>
    <input name="name" class="form-control mat-input @error('name') is-invalid @enderror"
           value="{{ old('name', $company->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">CUIT</label>
    <input name="cuit" class="form-control mat-input @error('cuit') is-invalid @enderror"
           value="{{ old('cuit', $company->cuit ?? '') }}">
    @error('cuit')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control mat-input @error('email') is-invalid @enderror"
           value="{{ old('email', $company->email ?? '') }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Teléfono</label>
    <input name="telefono" class="form-control mat-input" value="{{ old('telefono', $company->telefono ?? '') }}">
  </div>

  <div class="col-12">
    <label class="form-label">Dirección</label>
    <input name="direccion" class="form-control mat-input" value="{{ old('direccion', $company->direccion ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Logo</label>
    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if(!empty($company?->logo))
      <div class="small text-muted mt-1">Actual: {{ $company->logo }}</div>
    @endif
  </div>

  <div class="col-md-3">
    <label class="form-label">Color primario</label>
    <input name="color_primario" class="form-control mat-input"
           value="{{ old('color_primario', $company->color_primario ?? '') }}" placeholder="#10b981">
  </div>

  <div class="col-md-3">
    <label class="form-label">Color secundario</label>
    <input name="color_secundario" class="form-control mat-input"
           value="{{ old('color_secundario', $company->color_secundario ?? '') }}" placeholder="#1e88e5">
  </div>
</div>
