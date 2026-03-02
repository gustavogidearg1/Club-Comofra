@php
  // $row: PointReference
  // $companies: collection (solo si admin_sitio, si no puede venir vacío)
  $isSiteAdmin = auth()->user()?->hasRole('admin_sitio');
@endphp

<div class="row g-3">

  <div class="col-12 col-md-6">
    <label class="form-label fw-semibold">Nombre</label>
    <input name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $row->name) }}"
           maxlength="120"
           required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Ej: Vacaciones, Premio, Ajuste, Kit escolar.</div>
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label fw-semibold">Orden</label>
    <input type="number"
           name="sort_order"
           class="form-control @error('sort_order') is-invalid @enderror"
           value="{{ old('sort_order', $row->sort_order) }}"
           min="0" max="9999">
    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Menor = aparece primero.</div>
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label fw-semibold">Activa</label>
    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
<option value="1" @selected((int)old('is_active', $row->is_active ?? 1) === 1)>Sí</option>
<option value="0" @selected((int)old('is_active', $row->is_active ?? 1) === 0)>No</option>
    </select>
    @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Ámbito: Global vs Empresa --}}
  @if($isSiteAdmin)
    <div class="col-12">
      <label class="form-label fw-semibold">Ámbito</label>
      <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
        <option value="">Global (todas las empresas)</option>
        @foreach($companies as $c)
          <option value="{{ $c->id }}" @selected((string)old('company_id', $row->company_id) === (string)$c->id)>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
      @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      <div class="form-text">
        Global = aparece para todas las empresas. Si elegís una empresa, solo la ve esa empresa.
      </div>
    </div>
  @else
    <div class="col-12">
      <div class="alert alert-info mb-0">
        Esta referencia quedará asociada a tu empresa (admin_empresa).
      </div>
    </div>
  @endif

</div>
