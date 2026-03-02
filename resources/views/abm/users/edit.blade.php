@extends('layouts.app')

@section('title','Editar usuario - '.$user->name)

@section('content')
<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-person-gear"></i> Editar Usuario
    </h3>
    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('abm.users.index') }}" class="btn btn-outline-secondary btn-sm">Atrás</a>
      <a href="{{ route('abm.users.show', $user) }}" class="btn btn-outline-secondary btn-sm">Ver</a>
    </div>
  </div>

  <div class="card-body">
    <form id="user-form" method="POST"
          action="{{ route('abm.users.update', $user) }}"
          enctype="multipart/form-data" autocomplete="off">
      @csrf
      @method('PUT')

      {{-- =========================
           Datos básicos
      ========================== --}}

      <div class="row g-3">
        <div class="col-4">
          <label class="form-label">Name</label>
          <input name="name"
                 class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $user->name) }}"
                 required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <label class="form-label">Email</label>
          <input name="email"
                 type="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email', $user->email) }}"
                 required>
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <label class="form-label">CUIL</label>
          <input name="cuil"
                 class="form-control @error('cuil') is-invalid @enderror"
                 value="{{ old('cuil', $user->cuil) }}"
                 maxlength="13">
          @error('cuil') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-8">
          <label class="form-label">Dirección</label>
          <input name="direccion"
                 autocomplete="street-address"
                 autocapitalize="off"
                 spellcheck="false"
                 class="form-control @error('direccion') is-invalid @enderror"
                 value="{{ old('direccion', $user->direccion) }}">
          @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <label class="form-label">Teléfono</label>
          <input name="telefono"
                 class="form-control @error('telefono') is-invalid @enderror"
                 value="{{ old('telefono', $user->telefono) }}"
                 placeholder="+54 9 351 123-4567"
                 maxlength="30">
          @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- =========================
             Ubicación
        ========================== --}}
        <div class="col-4">
          <label class="form-label">Localidad</label>
          <select name="localidad_id" class="form-select @error('localidad_id') is-invalid @enderror">
            <option value="">—</option>
            @foreach($localidades as $loc)
              <option value="{{ $loc->id }}"
                @selected((string)old('localidad_id', $user->localidad_id)===(string)$loc->id)>
                {{ $loc->nombre }} @if($loc->cp) ({{ $loc->cp }}) @endif
              </option>
            @endforeach
          </select>
          @error('localidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <label class="form-label">Provincia</label>
          <select name="provincia_id" class="form-select @error('provincia_id') is-invalid @enderror">
            <option value="">—</option>
            @foreach($provincias as $prov)
              <option value="{{ $prov->id }}"
                @selected((string)old('provincia_id', $user->provincia_id)===(string)$prov->id)>
                {{ $prov->nombre }}
              </option>
            @endforeach
          </select>
          @error('provincia_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <label class="form-label">País</label>
          <select name="pais_id" class="form-select @error('pais_id') is-invalid @enderror">
            <option value="">—</option>
            @foreach($paises as $p)
              <option value="{{ $p->id }}"
                @selected((string)old('pais_id', $user->pais_id)===(string)$p->id)>
                {{ $p->nombre }}
              </option>
            @endforeach
          </select>
          @error('pais_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- =========================
             Company + Estado
        ========================== --}}
        @php
          use Illuminate\Support\Collection;

          $authUser       = auth()->user();
          $isSiteAdmin    = $authUser && $authUser->hasRole('admin_sitio');
          $isCompanyAdmin = $authUser && $authUser->hasRole('admin_empresa');
          $canEditRoles   = $isSiteAdmin || $isCompanyAdmin;

          $selectedRoles = old('roles', $currentRoleNames ?? $user->roles->pluck('name')->toArray());

          if (!$isSiteAdmin && $isCompanyAdmin && empty($selectedRoles)) {
            $selectedRoles = ['empleado'];
          }

          $roleLabels = [
            'admin_sitio'    => 'Administrador del sitio',
            'admin_empresa'  => 'Administrador de compañía',
            'negocio'        => 'Negocio / empleado interno',
            'empleado'       => 'Empleado',
          ];

          $roleList = $roles instanceof Collection ? $roles->all() : (array)$roles;

          $availableRoles = $roleList;
          if ($isCompanyAdmin && !$isSiteAdmin) {
            $availableRoles = array_values(array_filter($roleList, fn($rname) => $rname !== 'admin_sitio'));
          }
        @endphp

        @if($isSiteAdmin)
          <div class="col-6">
            <label class="form-label">Company</label>
            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
              <option value="">—</option>
              @foreach($companies as $c)
                <option value="{{ $c->id }}"
                  @selected((string)old('company_id', $user->company_id)===(string)$c->id)>
                  {{ $c->name }}
                </option>
              @endforeach
            </select>
            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        @else
          <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
        @endif

        <div class="col-3">
          <label class="form-label">Activo</label>
          <select name="activo" class="form-select @error('activo') is-invalid @enderror">
            <option value="1" @selected(old('activo', (int)$user->activo)==1)>Sí</option>
            <option value="0" @selected(old('activo', (int)$user->activo)==0)>No</option>
          </select>
          @error('activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-3">
          <label class="form-label">Fecha nacimiento</label>
          <input type="date"
                 name="fecha_nacimiento"
                 class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                 value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d')) }}">
          @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- =========================
             Imagen
        ========================== --}}
        <div class="col-6">
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
                   style="max-height:80px;border-radius:12px;">
            </div>
          @endif
        </div>

        {{-- =========================
             Password
        ========================== --}}
        <div class="col-6">
          <label class="form-label">Nueva contraseña (opcional)</label>
          <input name="password"
                 type="password"
                 class="form-control @error('password') is-invalid @enderror"
                 placeholder="Dejar vacío para no cambiar">
          @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-6">
          <label class="form-label">Confirmar nueva contraseña</label>
          <input name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
        </div>

        {{-- =========================
             ROLES (checkbox)
        ========================== --}}
        <div class="col-12">
          <label class="form-label"><h3>Roles</h3></label>

          @if($canEditRoles)
            <div class="d-flex flex-wrap gap-2">
              @foreach($availableRoles as $rname)
                @php $label = $roleLabels[$rname] ?? $rname; @endphp
                <div class="form-check me-3">
                  <input class="form-check-input"
                         type="checkbox"
                         name="roles[]"
                         id="role_{{ $rname }}"
                         value="{{ $rname }}"
                         @checked(in_array($rname, $selectedRoles))>
                  <label class="form-check-label" for="role_{{ $rname }}">
                    {{ $label }}
                  </label>
                </div>
              @endforeach
            </div>
            @error('roles') <div class="text-danger small">{{ $message }}</div> @enderror
          @else
            @foreach($selectedRoles as $rname)
              <input type="hidden" name="roles[]" value="{{ $rname }}">
            @endforeach
          @endif
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('abm.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
          <button class="btn btn-primary btn-mat"><i class="bi bi-check2"></i> Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ===== Overlay (spinner + mensaje) ===== --}}
<style>
  .submit-overlay {
    position: fixed; inset: 0; z-index: 1050;
    display: none; align-items: center; justify-content: center;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(1.5px);
  }
  .submit-card {
    background: #fff; border-radius: 14px; padding: 24px 28px; text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,.2);
    max-width: 420px; width: 92%;
  }
  .submit-card .muted { color:#6c757d; font-size: .95rem; }
</style>

<div id="submitOverlay" class="submit-overlay">
  <div class="submit-card">
    <div class="spinner-border" role="status" style="width:4rem;height:4rem;"></div>
    <h5 class="mt-3 mb-1">Guardando usuario…</h5>
    <div class="muted">
      Guardando la información.<br>
      Por favor, esperá sin cerrar esta ventana.
    </div>
  </div>
</div>

<script>
(function () {
  const overlay = document.getElementById('submitOverlay');
  const form = document.getElementById('user-form');
  if (!form) return;

  const restoreButtons = () => {
    form.querySelectorAll('button, input[type="submit"]').forEach(el => {
      el.disabled = false;
      if (el.type === 'submit' && el.dataset._oldText) {
        el.innerHTML = el.dataset._oldText;
        delete el.dataset._oldText;
      }
    });
  };

  form.addEventListener('submit', function () {
    if (!form.checkValidity()) return;

    form.querySelectorAll('button, input[type="submit"]').forEach(el => {
      el.disabled = true;
      if (el.type === 'submit') {
        el.dataset._oldText = el.innerHTML;
        el.innerHTML = 'Enviando…';
      }
    });

    overlay.style.display = 'flex';
  });

  @if ($errors->any())
  window.addEventListener('DOMContentLoaded', () => {
    overlay.style.display = 'none';
    restoreButtons();
  });
  @endif
})();
</script>

@endsection
