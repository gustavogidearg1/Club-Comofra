@extends('layouts.app')

@section('title', 'Crear movimiento de puntos')

@push('styles')
<style>
  .mat-card{
    border-radius:16px;
    box-shadow:0 6px 18px rgba(15,23,42,.12);
    border:0;
  }
  .mat-header{
    display:flex;
    align-items:center;
    gap:.75rem;
    padding:.9rem 1rem;
    border-bottom:1px solid rgba(0,0,0,.06);
    background:transparent;
  }
  .mat-title{
    font-weight:800;
    font-size:1.05rem;
    margin:0;
    color:#0f172a;
  }
  .btn-mat{
    border-radius:999px;
    padding:.5rem .95rem;
    font-weight:700;
  }
</style>
@endpush

@section('content')
<x-flash />

<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title">
      <i class="bi bi-plus-circle me-1"></i> Crear movimiento de puntos
    </h3>

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="card-body">
    <form id="pointsCreateForm" method="POST" action="{{ route('points.store') }}" class="row g-3">
      @csrf

      {{-- COMPANY (solo admin_sitio) --}}
      @if($isSiteAdmin)
        <div class="col-12 col-md-4">
          <label class="form-label">Compañía (opcional)</label>
          <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
            <option value="">Tomar desde el empleado</option>
            @foreach($companies as $c)
              <option value="{{ $c->id }}" @selected(old('company_id', $companyId) == $c->id)>
                {{ $c->name }}
              </option>
            @endforeach
          </select>
          <div class="form-text">Si no elegís compañía, se toma automáticamente la del empleado.</div>
          @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      @endif

      {{-- EMPLEADO --}}
      <div class="col-12 col-md-{{ $isSiteAdmin ? '8' : '6' }}">
        <label class="form-label">Empleado</label>

        <div class="input-group">
          <select id="employee_user_id"
                  name="employee_user_id"
                  class="form-select @error('employee_user_id') is-invalid @enderror"
                  required>
            <option value="">Seleccionar empleado…</option>
            @foreach($employees as $e)
              <option value="{{ $e->id }}"
                      data-name="{{ $e->name }}"
                      data-cuil="{{ $e->cuil ?? '' }}"
                      data-company="{{ $e->company->name ?? '' }}"
                      @selected(old('employee_user_id') == $e->id)>
                {{ $e->name }} — {{ $e->cuil ?? 'sin CUIL' }}

              </option>
            @endforeach
          </select>

          <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEmployeeSearch"
                  @if(!$isSiteAdmin)
                    data-fixed-company="{{ auth()->user()->company->name ?? '' }}"
                  @endif
          >
            <i class="bi bi-search"></i>
          </button>
        </div>

        @error('employee_user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div class="form-text">
          Podés seleccionar desde el combo o buscar por nombre / CUIL
        </div>
      </div>

      {{-- TIPO --}}
      <div class="col-12 col-md-3">
        <label class="form-label">Tipo</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
          @foreach($types as $k => $label)
            <option value="{{ $k }}" @selected(old('type', 'earn') == $k)>{{ $label }}</option>
          @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- PUNTOS --}}
      <div class="col-12 col-md-3">
        <label class="form-label">Puntos</label>
        <input type="number"
               min="1"
               name="points"
               class="form-control @error('points') is-invalid @enderror"
               value="{{ old('points', 10) }}"
               required>

        @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- FECHA/HORA --}}
      <div class="col-12 col-md-3">
        <label class="form-label">Fecha y hora</label>
        <input type="datetime-local"
               name="occurred_at"
               class="form-control @error('occurred_at') is-invalid @enderror"
               value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}">
        <div class="form-text">Por defecto se carga la fecha/hora actual (podés cambiarla).</div>
        @error('occurred_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- REFERENCIA (OBLIGATORIA) --}}
      <div class="col-12 col-md-6">
        <label class="form-label">Referencia <span class="text-danger">*</span></label>

        {{-- Filtro de referencias SOLO admin_sitio (GET) --}}
        @if($isSiteAdmin)
          <div class="mb-2">
            <label class="form-label">Compañía (para referencias)</label>

            {{-- OJO: esto NO va al POST, solo filtra por GET --}}
            <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2">
              <select name="ref_company_id" class="form-select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach($companies as $c)
                  <option value="{{ $c->id }}" @selected((string)request('ref_company_id') === (string)$c->id)>
                    {{ $c->name }}
                  </option>
                @endforeach
              </select>
              @foreach(request()->except('ref_company_id') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
              @endforeach
            </form>

            <div class="form-text">Esto filtra las referencias disponibles.</div>
          </div>
        @endif

        <select name="reference_id"
                class="form-select @error('reference_id') is-invalid @enderror"
                required>
          <option value="">Seleccionar referencia…</option>

          @forelse($references as $ref)
            <option value="{{ $ref->id }}" @selected(old('reference_id') == $ref->id)>
              {{ $ref->name }}
            </option>
          @empty
            <option value="" disabled>
              (No hay referencias activas cargadas en point_references)
            </option>
          @endforelse
        </select>

        <div class="form-text">La referencia es obligatoria.</div>
        @error('reference_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- NOTA --}}
      <div class="col-12">
        <label class="form-label">Nota (opcional)</label>
        <textarea name="note"
                  rows="3"
                  class="form-control @error('note') is-invalid @enderror"
                  maxlength="500"
                  placeholder="Detalle interno (opcional)">{{ old('note') }}</textarea>
        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- ACTIONS --}}
      <div class="col-12 d-flex flex-wrap gap-2">
<button id="btnSubmitMovement" type="submit" class="btn btn-primary btn-mat">
  <i class="bi bi-check2"></i> Guardar movimiento
</button>
        <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
          Cancelar
        </a>
      </div>

      {{-- Ayuda si no hay referencias --}}
      @if(($references ?? collect())->isEmpty())
        <div class="col-12">
          <div class="alert alert-warning mb-0">
            <div class="fw-semibold mb-1">
              <i class="bi bi-exclamation-triangle"></i> No hay referencias disponibles
            </div>
            Cargá referencias en la tabla <code>point_references</code> (por ejemplo: Sueldo, Premios, Vacaciones, Ajuste, etc.)
            y marcá <code>is_active = 1</code>. Luego volvé a esta pantalla.
          </div>
        </div>
      @endif

    </form>
  </div>
</div>

{{-- MODAL: Buscar empleado --}}
<div class="modal fade" id="modalEmployeeSearch" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-search me-1"></i> Buscar empleado
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <div class="row g-3 align-items-end mb-2">
          <div class="col-12 col-md-6">
            <label class="form-label">Buscar</label>
            <input type="text" id="empSearchInput" class="form-control"
                   placeholder="Escribí nombre o CUIL">
          </div>

          @if($isSiteAdmin)
            <div class="col-12 col-md-3">
              <label class="form-label">Filtrar por compañía</label>
              <select id="empCompanyFilter" class="form-select">
                <option value="">Todas</option>
                @php
                  $companyNames = ($employees ?? collect())
                    ->map(fn($e) => $e->company->name ?? null)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
                @endphp
                @foreach($companyNames as $cn)
                  <option value="{{ $cn }}">{{ $cn }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="col-12 col-md-3 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary w-100" id="empClearBtn">
              <i class="bi bi-x-circle"></i> Limpiar
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Empleado</th>
                <th class="text-nowrap">CUIL</th>
                <th>Compañía</th>
                <th class="text-end" style="width:140px;">Acción</th>
              </tr>
            </thead>
            <tbody id="empResultsBody"></tbody>
          </table>
        </div>

        <div class="text-muted small mt-2" id="empResultsInfo"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-mat" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Enviando movimiento --}}
<div class="modal fade" id="modalSubmitting" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-body p-4">
        <div class="d-flex align-items-start gap-3">
          <div class="spinner-border" role="status" aria-label="Enviando"></div>

          <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:1.05rem;">Enviando y cargando el movimiento…</div>
            <div class="text-muted mt-1">
              Por favor <b>no cierres esta pantalla</b>. Esto puede demorar unos segundos.
            </div>
            <div class="text-muted small mt-2">
              Si refrescás o volvés atrás, el movimiento podría no guardarse correctamente.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



@push('scripts')
<script>
(function(){
  // =========================
  // MODAL BUSCAR EMPLEADO
  // =========================
  const employeeSelect = document.getElementById('employee_user_id');
  const empModalEl     = document.getElementById('modalEmployeeSearch');

  const searchInput = document.getElementById('empSearchInput');
  const companySel  = document.getElementById('empCompanyFilter');
  const clearBtn    = document.getElementById('empClearBtn');
  const bodyEl      = document.getElementById('empResultsBody');
  const infoEl      = document.getElementById('empResultsInfo');

  const openBtn = document.querySelector('[data-bs-target="#modalEmployeeSearch"]');
  const fixedCompany = openBtn?.dataset.fixedCompany || '';

  const hasEmployeeModal =
    employeeSelect && empModalEl && searchInput && clearBtn && bodyEl && infoEl;

  let employees = [];

  function normalize(s){
    return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function escapeHtml(str){
    return (str || '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function renderEmployees(){
    if (!hasEmployeeModal) return;

    const q = normalize(searchInput.value);
    const company = fixedCompany || (companySel ? companySel.value : '');

    const filtered = employees.filter(e => {
      const hay = normalize([e.name, e.cuil, e.company].join(' '));
      const okQ = !q || hay.includes(q);
      const okC = !company || e.company === company;
      return okQ && okC;
    });

    bodyEl.innerHTML = '';

    filtered.slice(0, 200).forEach(e => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><div class="fw-semibold">${escapeHtml(e.name)}</div></td>
        <td class="text-nowrap">${escapeHtml(e.cuil || '—')}</td>
        <td>${escapeHtml(e.company || '—')}</td>
        <td class="text-end">
          <button type="button" class="btn btn-primary btn-sm" data-emp-id="${e.id}">
            Seleccionar
          </button>
        </td>
      `;
      bodyEl.appendChild(tr);
    });

    const shown = Math.min(filtered.length, 200);
    infoEl.textContent = filtered.length
      ? `Mostrando ${shown} de ${filtered.length} resultados.`
      : 'No se encontraron resultados con ese filtro.';
  }

  if (hasEmployeeModal) {
    // Dataset desde el select (sin AJAX)
    employees = Array.from(employeeSelect.querySelectorAll('option'))
      .filter(o => o.value)
      .map(o => ({
        id: o.value,
        name: (o.dataset.name || o.textContent || '').trim(),
        cuil: (o.dataset.cuil || '').trim(),
        company: (o.dataset.company || '').trim()
      }));

    bodyEl.addEventListener('click', (ev) => {
      const btnSel = ev.target.closest('button[data-emp-id]');
      if(!btnSel) return;

      const id = btnSel.getAttribute('data-emp-id');
      employeeSelect.value = id;
      employeeSelect.dispatchEvent(new Event('change', {bubbles:true}));

      // Cerrar modal sin window.bootstrap
      const closeBtn = empModalEl.querySelector('[data-bs-dismiss="modal"]');
      if (closeBtn) closeBtn.click();
    });

    searchInput.addEventListener('input', renderEmployees);
    if (companySel) companySel.addEventListener('change', renderEmployees);

    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      if (companySel) companySel.value = '';
      renderEmployees();
      searchInput.focus();
    });

    empModalEl.addEventListener('shown.bs.modal', () => {
      renderEmployees();
      searchInput.focus();
      searchInput.select();
    });
  }

  // =========================
  // MODAL ENVIANDO / SUBMIT
  // =========================
  const form          = document.getElementById('pointsCreateForm');
  const submitBtn     = document.getElementById('btnSubmitMovement');
  const submitModalEl = document.getElementById('modalSubmitting');

  if (form && submitBtn && submitModalEl) {
    let submitting = false;

    form.addEventListener('submit', function(ev){
      if (submitting) {
        ev.preventDefault();
        return;
      }
      submitting = true;

      // Evitar doble click
      submitBtn.disabled = true;
      submitBtn.classList.add('disabled');

      // Mostrar modal (sin depender de window.bootstrap)
      const tmp = document.createElement('button');
      tmp.type = 'button';
      tmp.setAttribute('data-bs-toggle', 'modal');
      tmp.setAttribute('data-bs-target', '#modalSubmitting');
      tmp.style.display = 'none';
      document.body.appendChild(tmp);
      tmp.click();
      tmp.remove();
    });

    // Si el navegador vuelve atrás (bfcache), re-habilitar
    window.addEventListener('pageshow', function(){
      submitting = false;
      submitBtn.disabled = false;
      submitBtn.classList.remove('disabled');
    });
  }
})();
</script>
@endpush


@endsection
