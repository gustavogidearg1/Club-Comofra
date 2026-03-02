<x-guest-layout>
@php
  $appName = config('app.name', 'Club Comofra');
@endphp

<style>
html, body { height: 100%; }
body { margin: 0; }

:root{
  --mp-primary:   #FF9900; /* principal */
  --mp-secondary: #585858; /* secundario */

  --mp-dark: #2E2E2E;
  --mp-bg:   #F5F6F7;

  --mp-shadow1: rgba(15,23,42,.18);
  --mp-shadow2: rgba(15,23,42,.12);
}

/* Fondo igual al login */
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

/* Card material */
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

/* Header gradiente */
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

/* Body */
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

/* Campos tipo material */
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

/* Botón */
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
      <p class="auth-title-sub">Recuperar contraseña</p>
    </div>

    {{-- BODY --}}
    <div class="auth-body">
      <h1 class="auth-form-title">¿Olvidaste tu contraseña?</h1>

      <div class="auth-help">
        Ingresá tu email y te vamos a enviar un link para restablecerla.
      </div>

      @if (session('status'))
        <div class="alert alert-success small mb-3">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger small mb-3">Revisá el email ingresado.</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" id="forgotForm" novalidate>
        @csrf

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

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-primary-mp" id="btnSubmit">
            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
            Enviar link de recuperación
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
  document.getElementById('forgotForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.querySelector('.spinner-border')?.classList.remove('d-none');
  });
</script>
</x-guest-layout>
