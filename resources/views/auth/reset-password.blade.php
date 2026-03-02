<x-guest-layout>
@php
  $appName = config('app.name', 'Club Comofra');
@endphp

<style>
html, body { height: 100%; }
body { margin: 0; }

:root{
  --mp-primary:   #FF9900;
  --mp-secondary: #585858;

  --mp-dark: #2E2E2E;
  --mp-bg:   #F5F6F7;

  --mp-shadow1: rgba(15,23,42,.18);
  --mp-shadow2: rgba(15,23,42,.12);
}

.auth-shell{
  min-height: 100vh;
  display:flex;
  align-items:flex-start;
  justify-content:center;
  padding: 5rem 1rem 2rem;
  background: linear-gradient(
    to bottom,
    var(--mp-secondary) 0%,
    var(--mp-secondary) 45%,
    var(--mp-bg) 45%,
    var(--mp-bg) 100%
  );
}

.auth-card{
  width: 100%;
  max-width: 460px;
  border-radius: 18px;
  border: 0;
  background: #fff;
  box-shadow:
    0 14px 28px var(--mp-shadow1),
    0 10px 10px var(--mp-shadow2);
  overflow: hidden;
}

.auth-header{
  padding: 1.6rem 1.5rem 1.2rem;
  background: linear-gradient(135deg, var(--mp-primary), var(--mp-secondary));
  color:#fff;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:.35rem;
  border-bottom: 1px solid rgba(255,255,255,.18);
}

.auth-logo{
  width: 74px;
  height: 74px;
  border-radius: 18px;
  object-fit: cover;
  background: rgba(255,255,255,.95);
  padding: 8px;
  box-shadow: 0 10px 24px rgba(0,0,0,.18);
}

.auth-title-main{
  font-weight: 800;
  letter-spacing:.08em;
  font-size: .78rem;
  text-transform: uppercase;
  opacity:.95;
  margin-top: .35rem;
}

.auth-title-sub{
  font-size: .86rem;
  opacity:.9;
  margin: 0;
  text-align:center;
}

.auth-body{
  padding: 1.15rem 1.75rem 1.5rem;
}

.auth-form-title{
  font-size: 1.05rem;
  font-weight: 800;
  text-align:center;
  margin: .25rem 0 .85rem;
  color: var(--mp-secondary);
}

.auth-help{
  font-size: .90rem;
  color: rgba(88,88,88,.85);
  margin-bottom: 1rem;
  text-align:center;
}

.mat-field{ margin-bottom: 1rem; }
.mat-label{
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .06em;
  font-weight: 700;
  color: rgba(88,88,88,.80);
  margin-bottom: .25rem;
}
.mat-input{
  border-radius: 14px;
  border: 1px solid rgba(46,46,46,.18);
  padding: .6rem .8rem;
  font-size: .92rem;
  transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
}
.mat-input:focus{
  border-color: var(--mp-primary);
  box-shadow: 0 0 0 .2rem rgba(255,153,0,.22);
  background-color:#f8fafc;
}

/* Toggle password */
.mat-password-wrapper{ position: relative; }
.mat-password-toggle{
  position:absolute;
  right:.35rem;
  top:50%;
  transform:translateY(-50%);
  border:0;
  background:transparent;
  padding:.25rem .45rem;
  border-radius:999px;
  color: rgba(88,88,88,.75);
}
.mat-password-toggle:hover{
  background: rgba(88,88,88,.10);
  color: var(--mp-secondary);
}
.mat-password-toggle i{ font-size: 1rem; }

.btn-primary-mp{
  border-radius: 999px;
  font-weight: 700;
  padding: .62rem 1rem;
  background: var(--mp-primary);
  border-color: var(--mp-primary);
  box-shadow: 0 6px 18px rgba(255,153,0,.22);
  color: #fff;
}
.btn-primary-mp:hover{
  background: #e58900;
  border-color: #e58900;
  box-shadow: 0 10px 22px rgba(255,153,0,.28);
  transform: translateY(-1px);
}

.auth-footer{
  background: rgba(0,0,0,.02);
  border-top: 1px solid rgba(0,0,0,.06);
  padding: .85rem 1rem;
  text-align:center;
  font-size: .82rem;
  color: rgba(88,88,88,.75);
}

.auth-links{
  display:flex;
  justify-content:center;
  gap:.75rem;
  margin-top: .9rem;
  font-size:.88rem;
}
.auth-links a{
  color: var(--mp-primary);
  font-weight: 700;
  text-decoration:none;
}
.auth-links a:hover{ text-decoration: underline; }

@media (max-width: 576px){
  .auth-shell{ padding: 2.6rem .75rem 1.5rem; }
  .auth-body{ padding: 1.1rem 1.15rem 1.2rem; }
}
</style>

<div class="auth-shell">
  <div class="auth-card">

    {{-- HEADER --}}
    <div class="auth-header">
      <img src="{{ asset('logos/ImgLogoCircular-SF.png') }}"
           alt="Logo {{ $appName }}"
           class="auth-logo">
      <div class="auth-title-main">{{ strtoupper($appName) }}</div>
      <p class="auth-title-sub">Restablecer contraseña</p>
    </div>

    {{-- BODY --}}
    <div class="auth-body">
      <h1 class="auth-form-title">Nueva contraseña</h1>

      <div class="auth-help">
        Elegí una contraseña nueva y confirmala para finalizar.
      </div>

      @if ($errors->any())
        <div class="alert alert-danger small mb-3">Revisá los campos marcados.</div>
      @endif

      <form method="POST" action="{{ route('password.store') }}" id="resetForm" novalidate>
        @csrf

        {{-- Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mat-field">
          <label for="email" class="mat-label">Email</label>
          <input id="email"
                 type="email"
                 name="email"
                 value="{{ old('email', $request->email) }}"
                 required
                 autofocus
                 autocomplete="username"
                 class="form-control mat-input @error('email') is-invalid @enderror">
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Password --}}
        <div class="mat-field">
          <label for="password" class="mat-label">Contraseña nueva</label>
          <div class="mat-password-wrapper">
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="new-password"
                   class="form-control mat-input @error('password') is-invalid @enderror">
            <button type="button"
                    class="mat-password-toggle"
                    id="togglePassword"
                    aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mat-field">
          <label for="password_confirmation" class="mat-label">Confirmar contraseña</label>
          <div class="mat-password-wrapper">
            <input id="password_confirmation"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   class="form-control mat-input @error('password_confirmation') is-invalid @enderror">
            <button type="button"
                    class="mat-password-toggle"
                    id="togglePassword2"
                    aria-label="Mostrar u ocultar confirmación">
              <i class="bi bi-eye"></i>
            </button>
            @error('password_confirmation')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-primary-mp" id="btnSubmit">
            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
            Restablecer contraseña
          </button>
        </div>

        <div class="auth-links">
          <a href="{{ route('login') }}">Volver al login</a>
        </div>
      </form>
    </div>

    {{-- FOOTER --}}
    <div class="auth-footer">
      © {{ date('Y') }} {{ $appName }} — Todos los derechos reservados
    </div>

  </div>
</div>

<script>
  const toggle = (btnId, inputId) => {
    const btn = document.getElementById(btnId);
    const inp = document.getElementById(inputId);
    if (!btn || !inp) return;

    btn.addEventListener('click', () => {
      const isText = inp.type === 'text';
      inp.type = isText ? 'password' : 'text';
      btn.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
  };

  toggle('togglePassword', 'password');
  toggle('togglePassword2', 'password_confirmation');

  document.getElementById('resetForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.querySelector('.spinner-border')?.classList.remove('d-none');
  });
</script>
</x-guest-layout>
