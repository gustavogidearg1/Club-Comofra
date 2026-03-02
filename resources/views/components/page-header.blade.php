@props([
  'title' => 'Title',
  'subtitle' => null,
])

<div class="card mat-card mb-3">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      {{ $title }}
    </h3>

    @if($subtitle)
      <div class="text-muted small ms-2">
        {{ $subtitle }}
      </div>
    @endif

    <div class="ms-auto">
      {{ $actions ?? '' }}
    </div>
  </div>
</div>
