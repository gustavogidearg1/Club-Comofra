@php
    $esEdicion = isset($oferta);
@endphp

<div class="card mat-card shadow-sm border-0 mb-4">
    <div class="card-header mat-header bg-white d-flex align-items-center">
        <h3 class="mat-title mb-0">
            <i class="fas fa-tags me-2"></i>
            {{ $esEdicion ? 'Editar oferta' : 'Nueva oferta' }}
        </h3>
    </div>

    <div class="card-body">
        <div class="row g-3">

            {{-- TITULO / ORDEN --}}
            <div class="col-md-8">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control"
                       value="{{ old('titulo', $oferta->titulo ?? '') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Orden</label>
                <input type="number" name="orden" class="form-control"
                       value="{{ old('orden', $oferta->orden ?? 0) }}" min="0">
            </div>

            {{-- PRECIOS --}}
            <div class="col-md-6">
                <label class="form-label">Precio</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control"
                       value="{{ old('precio', $oferta->precio ?? '') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Precio anterior</label>
                <input type="number" step="0.01" min="0" name="precio_anterior" class="form-control"
                       value="{{ old('precio_anterior', $oferta->precio_anterior ?? '') }}">
            </div>

            {{-- FECHAS --}}
            <div class="col-md-6">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control"
                       value="{{ old('fecha_desde',
                            isset($oferta?->fecha_desde)
                                ? $oferta->fecha_desde->format('Y-m-d')
                                : now()->format('Y-m-d')
                       ) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control"
                       value="{{ old('fecha_hasta',
                            isset($oferta?->fecha_hasta)
                                ? $oferta->fecha_hasta->format('Y-m-d')
                                : ''
                       ) }}">
            </div>

            {{-- DESCRIPCIONES --}}
            <div class="col-md-12">
                <label class="form-label">Descripción corta</label>
                <input type="text" name="descripcion_corta" class="form-control"
                       value="{{ old('descripcion_corta', $oferta->descripcion_corta ?? '') }}">
            </div>

            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" rows="4" class="form-control">{{ old('descripcion', $oferta->descripcion ?? '') }}</textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" rows="3" class="form-control">{{ old('observaciones', $oferta->observaciones ?? '') }}</textarea>
            </div>

            {{-- ESTADO --}}
            <div class="col-md-4">
                <label class="form-label">Estado *</label>
                <select name="estado" class="form-select" required>
                    @php
                        $estadoActual = old('estado', $oferta->estado ?? 'publicada');
                    @endphp
                    <option value="borrador" {{ $estadoActual == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicada" {{ $estadoActual == 'publicada' ? 'selected' : '' }}>Publicada</option>
                    <option value="pausada" {{ $estadoActual == 'pausada' ? 'selected' : '' }}>Pausada</option>
                    <option value="vencida" {{ $estadoActual == 'vencida' ? 'selected' : '' }}>Vencida</option>
                </select>
            </div>

            {{-- OPCIONES --}}
            <div class="col-md-8 d-flex align-items-end gap-4 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="destacada" value="1"
                           id="destacada"
                           {{ old('destacada', $oferta->destacada ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="destacada">Destacada</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="enviar_correo" value="1"
                           id="enviar_correo"
                           {{ old('enviar_correo', $oferta->enviar_correo ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enviar_correo">Enviar por correo</label>
                </div>
            </div>

            {{-- IMAGENES --}}
            @if($esEdicion)

                <div class="col-md-12">
                    <label class="form-label">Agregar nuevas imágenes</label>
                    <input type="file" name="imagenes_nuevas[]" class="form-control" multiple accept="image/*">
                    <small class="text-muted">Podés agregar más imágenes sin perder las actuales.</small>
                </div>

                @if($oferta->imagenes->count())
                    <div class="col-md-12">
                        <label class="form-label d-block">Imágenes actuales</label>

                        <div class="row g-3">
                            @foreach($oferta->imagenes as $imagen)
                                <div class="col-md-3">
                                    <div class="card h-100">
                                        <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                             class="card-img-top"
                                             style="height: 180px; object-fit: cover;">

                                        <div class="card-body p-2">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="eliminar_imagenes[]"
                                                       value="{{ $imagen->id }}">
                                                <label class="form-check-label">Eliminar</label>
                                            </div>

                                            @if($imagen->principal)
                                                <span class="badge bg-primary mt-2">Principal</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endif

            @else

                <div class="col-md-12">
                    <label class="form-label">Imágenes *</label>

                    <input type="file"
                           name="imagenes[]"
                           id="imagenes"
                           class="form-control"
                           multiple
                           accept="image/*"
                           required>

                    <small class="text-muted d-block mt-1">
                        Seleccioná al menos 1 imagen (podés elegir varias).
                    </small>

                    <div id="imagenes-info" class="small mt-2 text-secondary"></div>
                </div>

            @endif

        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary btn-mat">
        <i class="fas fa-save me-1"></i> Guardar
    </button>
</div>
