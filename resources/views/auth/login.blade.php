<x-guest-layout>
@php
  $appName = config('app.name', 'Club Comofra');
@endphp

<style>
html, body { height: 100%; }
body { margin: 0; }

:root{
  /* Nueva paleta */
  --mp-primary:   #FF9900; /* principal */
  --mp-secondary: #585858; /* secundario */

  /* Neutros */
  --mp-dark: #2E2E2E;
  --mp-bg:   #F5F6F7;

  --mp-shadow1: rgba(15,23,42,.18);
  --mp-shadow2: rgba(15,23,42,.12);
}

/* Fondo de pantalla: barra superior (secundario) + resto gris claro */
.auth-shell{
  min-height: 100vh;
  display:flex;
  align-items:flex-start;
  justify-content:center;
  padding: 5rem 1rem 2rem;
  background: linear-gradient(
    to bottom,
    var(--mp-primary) 0%,
    var(--mp-primary) 45%,
    var(--mp-bg) 45%,
    var(--mp-bg) 100%
  );
}

/* Card material */
.login-card{
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

/* Header con gradiente NUEVO (principal + secundario) */
.login-header{
  padding: 1.6rem 1.5rem 1.2rem;
  background: var(--mp-secondary);
  color:#fff;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:.35rem;
  border-bottom: 1px solid rgba(255,255,255,.18);
}

.login-logo{
  width: 74px;
  height: 74px;
  border-radius: 18px;
  object-fit: cover;
  background: rgba(255,255,255,.95);
  padding: 8px;
  box-shadow: 0 10px 24px rgba(0,0,0,.18);
}

.login-title-main{
  font-weight: 800;
  letter-spacing:.08em;
  font-size: .78rem;
  text-transform: uppercase;
  opacity:.95;
  margin-top: .35rem;
}
.login-title-sub{
  font-size: .86rem;
  opacity:.9;
  margin: 0;
}

.login-body{
  padding: 1.15rem 1.75rem 1.5rem;
}

.login-form-title{
  font-size: 1.05rem;
  font-weight: 800;
  text-align:center;
  margin: .25rem 0 1.15rem;
  color: var(--mp-secondary);
}

/* Campos tipo “material” */
.mat-field{ margin-bottom: 1rem; }
.mat-label{
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .06em;
  font-weight: 700;
  color: rgba(88,88,88,.80); /* secundario suavizado */
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

.login-options{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:.75rem;
  margin-bottom:1.1rem;
  font-size:.88rem;
}

/* Botón principal */
.btn-login{
  border-radius: 999px;
  font-weight: 700;
  padding: .62rem 1rem;
  background: var(--mp-primary);
  border-color: var(--mp-primary);
  box-shadow: 0 6px 18px rgba(255,153,0,.22);
  color: #fff;
}
.btn-login:hover{
  background: #e58900;     /* un poco más oscuro */
  border-color: #e58900;
  box-shadow: 0 10px 22px rgba(255,153,0,.28);
  transform: translateY(-1px);
}

.login-footer{
  background: rgba(0,0,0,.02);
  border-top: 1px solid rgba(0,0,0,.06);
  padding: .85rem 1rem;
  text-align:center;
  font-size: .82rem;
  color: rgba(88,88,88,.75);
}

@media (max-width: 576px){
  .auth-shell{ padding: 2.6rem .75rem 1.5rem; }
  .login-body{ padding: 1.1rem 1.15rem 1.2rem; }
}
</style>


<div class="auth-shell">
  <div class="login-card">

    {{-- HEADER --}}
    <div class="login-header">
      <img src="{{ asset('logos/ImgLogoCircular-SF.png') }}"
           alt="Logo {{ $appName }}"
           class="login-logo">
     <div class="login-title-main">{{ strtoupper($appName) }}</div>

      <p class="login-title-sub">Ingresá con tu cuenta</p>
    </div>

    {{-- BODY --}}
    <div class="login-body">
      <h1 class="login-form-title">Acceso</h1>

      @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger small">Revisá los campos marcados.</div>
      @endif

      <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mat-field">
          <label for="email" class="mat-label">Email</label>
          <input id="email"
                 type="email"
                 name="email"
                 value="{{ old('email') }}"
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
          <label for="password" class="mat-label">Contraseña</label>
          <div class="mat-password-wrapper">
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password"
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

        {{-- Recordarme / Olvidé --}}
        <div class="login-options">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="remember" id="remember_me"
                   {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember_me">Recordarme</label>
          </div>

          @if (Route::has('password.request'))
<a class="small fw-semibold" style="color: var(--mp-primary);" href="{{ route('password.request') }}">
  ¿Olvidaste tu contraseña?
</a>
          @endif
        </div>

        {{-- Botón ingresar --}}
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-login" id="btnSubmit">
            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
            <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
          </button>
        </div>
      </form>
    </div>

    {{-- FOOTER --}}
    <div class="login-footer">
      © {{ date('Y') }} {{ $appName }} — Todos los derechos reservados
    </div>

  </div>
</div>

<script>
  document.getElementById('togglePassword')?.addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const isText = pwd.type === 'text';
    pwd.type = isText ? 'password' : 'text';
    this.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
  });

  document.getElementById('loginForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.querySelector('.spinner-border')?.classList.remove('d-none');
  });
</script>
</x-guest-layout>
