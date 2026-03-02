@extends('layouts.app')

@section('title','Mi perfil')

@section('content')
@php
  $user = auth()->user();
@endphp

<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-person-circle"></i> Mi Perfil
    </h3>
    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="card-body">

    {{-- ======= Alertas ======= --}}
    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">Revisá los campos marcados.</div>
    @endif

    <div class="row g-3">

      {{-- =========================
           DATOS PERSONALES
      ========================== --}}
      <div class="col-12 col-lg-7">
        <div class="card border-0" style="box-shadow:none;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-card-text"></i>
            <strong>Datos</strong>
          </div>

          <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PATCH')

            <div class="row g-3">

              <div class="col-12 col-md-6">
                <label class="form-label">Nombre</label>
                <input name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}"
                       required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Email</label>
                <input name="email"
                       type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}"
                       required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">CUIL</label>
                <input name="cuil"
                       class="form-control @error('cuil') is-invalid @enderror"
                       value="{{ old('cuil', $user->cuil) }}"
                       maxlength="13">
                @error('cuil') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Teléfono</label>
                <input name="telefono"
                       class="form-control @error('telefono') is-invalid @enderror"
                       value="{{ old('telefono', $user->telefono) }}"
                       placeholder="+54 9 351 123-4567"
                       maxlength="30">
                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12">
                <label class="form-label">Dirección</label>
                <input name="direccion"
                       class="form-control @error('direccion') is-invalid @enderror"
                       value="{{ old('direccion', $user->direccion) }}"
                       autocomplete="street-address">
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">País</label>
                <select name="pais_id" class="form-select @error('pais_id') is-invalid @enderror">
                  <option value="">—</option>
                  @foreach($paises as $p)
                    <option value="{{ $p->id }}" @selected((string)old('pais_id', $user->pais_id)===(string)$p->id)>
                      {{ $p->nombre }}
                    </option>
                  @endforeach
                </select>
                @error('pais_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Provincia</label>
                <select name="provincia_id" class="form-select @error('provincia_id') is-invalid @enderror">
                  <option value="">—</option>
                  @foreach($provincias as $prov)
                    <option value="{{ $prov->id }}" @selected((string)old('provincia_id', $user->provincia_id)===(string)$prov->id)>
                      {{ $prov->nombre }}
                    </option>
                  @endforeach
                </select>
                @error('provincia_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Localidad</label>
                <select name="localidad_id" class="form-select @error('localidad_id') is-invalid @enderror">
                  <option value="">—</option>
                  @foreach($localidades as $loc)
                    <option value="{{ $loc->id }}" @selected((string)old('localidad_id', $user->localidad_id)===(string)$loc->id)>
                      {{ $loc->nombre }} @if($loc->cp) ({{ $loc->cp }}) @endif
                    </option>
                  @endforeach
                </select>
                @error('localidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date"
                       name="fecha_nacimiento"
                       class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                       value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d')) }}">
                @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Imagen</label>
                <input type="file"
                       name="imagen"
                       class="form-control @error('imagen') is-invalid @enderror"
                       accept="image/*">
                @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if($user->imagen)
                  <div class="mt-2">
                    <div class="small text-muted mb-1">Actual:</div>
                    <img src="{{ asset('storage/'.$user->imagen) }}"
                         alt="User image"
                         style="max-height:90px;border-radius:12px;">
                  </div>
                @endif
              </div>

              <div class="col-12 d-flex justify-content-end gap-2 mt-1">
                <button class="btn btn-primary btn-mat">
                  <i class="bi bi-check2"></i> Guardar datos
                </button>
              </div>

            </div>
          </form>
        </div>
      </div>

      {{-- =========================
           CAMBIAR CONTRASEÑA
      ========================== --}}
      <div class="col-12 col-lg-5">
        <div class="card border-0" style="box-shadow:none;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-shield-lock"></i>
            <strong>Seguridad</strong>
          </div>

          <form id="passwordForm" method="POST" action="{{ route('profile.password') }}" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Contraseña actual</label>
                <div class="input-group">
                  <input type="password"
                         name="current_password"
                         class="form-control @error('current_password') is-invalid @enderror"
                         autocomplete="current-password"
                         required>
                  <button class="btn btn-outline-secondary" type="button" data-toggle-pass="current_password">
                    <i class="bi bi-eye"></i>
                  </button>
                  @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Nueva contraseña</label>
                <div class="input-group">
                  <input type="password"
                         name="password"
                         class="form-control @error('password') is-invalid @enderror"
                         autocomplete="new-password"
                         required>
                  <button class="btn btn-outline-secondary" type="button" data-toggle-pass="password">
                    <i class="bi bi-eye"></i>
                  </button>
                  @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Confirmar nueva contraseña</label>
                <div class="input-group">
                  <input type="password"
                         name="password_confirmation"
                         class="form-control"
                         autocomplete="new-password"
                         required>
                  <button class="btn btn-outline-secondary" type="button" data-toggle-pass="password_confirmation">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>

              <div class="col-12 d-grid">
                <button class="btn btn-primary btn-mat">
                  <i class="bi bi-key"></i> Cambiar contraseña
                </button>
              </div>
            </div>
          </form>

          <hr class="my-4">

          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle text-danger mt-1"></i>
            <div>
              <div class="fw-bold">Eliminar cuenta</div>
              <div class="text-muted small">Acción irreversible. Solo si realmente corresponde.</div>

              <form method="POST" action="{{ route('profile.destroy') }}" class="mt-2"
                    onsubmit="return confirm('¿Seguro que querés eliminar tu cuenta? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i> Eliminar mi cuenta
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
  // Toggle show/hide password
  document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-toggle-pass');
      const input = document.querySelector(`input[name="${name}"]`);
      if (!input) return;

      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
  });
</script>
@endsection
