{{-- resources/views/welcome.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Club Comofra') }}</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    /* Pequeños estilos locales (mantienen estética “mat-card”) */
    .hero-wrap{
      min-height: 100vh;
      display: flex;
      align-items: center;
      background: radial-gradient(1200px 600px at 10% 10%, rgba(14,163,93,.15), transparent 55%),
                  radial-gradient(900px 500px at 90% 20%, rgba(13,110,253,.12), transparent 55%),
                  #f8f9fa;
    }
    .mat-card{
      border: 0;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.08);
    }
    .mat-header{
      display:flex;
      align-items:center;
      gap:.75rem;
      padding: 16px 18px;
      border-bottom: 1px solid rgba(0,0,0,.06);
      background: #fff;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
    }
    .mat-title{
      margin: 0;
      font-size: 1.1rem;
      font-weight: 700;
    }
    .brand-badge{
      width: 42px; height: 42px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
      background: rgba(14,163,93,.12);
      color: #0ea35d;
      font-size: 22px;
      flex: 0 0 auto;
    }
    .btn-mat{
      border-radius: 12px;
      padding: .6rem 1rem;
      font-weight: 600;
    }
    .muted{ color: #6c757d; }
    .feature{
      display:flex; gap:.75rem; align-items:flex-start;
    }
    .feature i{ font-size: 20px; margin-top: 2px; }
  </style>
</head>

<body>
  <div class="hero-wrap">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

          <div class="card mat-card">
            <div class="mat-header">
              <div class="brand-badge">
                  <img
            src="{{ asset('logos/ImgLogoCircular-SF.png') }}"
            alt="Logo {{ config('app.name') }}"
            width="36"
            height="36"
            style="border-radius:12px; object-fit:cover; box-shadow: 0 6px 16px rgba(0,0,0,.12);"
        />
              </div>

              <div>
                <h1 class="mat-title">{{ config('app.name','Club Comofra') }}</h1>

              </div>

              <div class="ms-auto d-flex gap-2">
                @auth
                  <a href="{{ route('dashboard') }}" class="btn btn-primary btn-mat">
                    Ir al panel <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                @else
                  @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-mat">
                      Iniciar sesión
                    </a>
                  @endif

                  @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-mat">
                      Registrarse
                    </a>
                  @endif
                @endauth
              </div>
            </div>

            <div class="card-body p-4 p-lg-5">
              <p class="mb-4 muted">
                Gestioná puntos, canjes y beneficios de forma ordenada.
                Un panel simple para operar, y una experiencia clara para el cliente.
              </p>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="feature">
                    <i class="bi bi-stars text-success"></i>
                    <div>
                      <div class="fw-semibold">Puntos y canjes</div>
                      <div class="small muted">Cargá beneficios, canjes destacados y premios.</div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="feature">
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                    <div>
                      <div class="fw-semibold">Control y seguimiento</div>
                      <div class="small muted">Historial, totales y métricas básicas.</div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="feature">
                    <i class="bi bi-shield-check text-secondary"></i>
                    <div>
                      <div class="fw-semibold">Accesos por roles</div>
                      <div class="small muted">Operador / Admin / cliente según tu modelo.</div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="feature">
                    <i class="bi bi-phone text-dark"></i>
                    <div>
                      <div class="fw-semibold">Diseño responsive</div>
                      <div class="small muted">Funciona bien en PC y celular.</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2">
                @auth
                  <a href="{{ route('dashboard') }}" class="btn btn-primary btn-mat">
                    Entrar al panel <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                @else
                  @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-mat">
                      Ya tengo cuenta
                    </a>
                  @endif
                  @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-mat">
                      Crear cuenta
                    </a>
                  @endif
                @endauth
              </div>
            </div>

            <div class="card-footer bg-white border-0 pt-0 pb-4 px-4 px-lg-5">
              <div class="small muted">
                © {{ date('Y') }} {{ config('app.name','Club Comofra') }} ·
                <span class="text-nowrap">creado idear</span>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
