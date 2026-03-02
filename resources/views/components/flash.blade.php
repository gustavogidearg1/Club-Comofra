@php
  $types = [
    'success' => 'success',
    'error'   => 'danger',
    'warning' => 'warning',
    'info'    => 'info',
  ];
@endphp

@foreach($types as $key => $bs)
  @if(session()->has($key))
    <div class="alert alert-{{ $bs }} alert-dismissible fade show mat-alert" role="alert">
      <div class="d-flex align-items-start gap-2">
        <div class="fw-semibold">{{ session($key) }}</div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
@endforeach
