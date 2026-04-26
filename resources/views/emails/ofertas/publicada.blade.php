<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva oferta</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Nueva oferta disponible</h2>

    <p><strong>{{ $oferta->titulo }}</strong></p>

    @if($oferta->descripcion_corta)
        <p>{{ $oferta->descripcion_corta }}</p>
    @endif

    @if($oferta->precio)
        <p>
            <strong>Precio:</strong>
            $ {{ number_format($oferta->precio, 2, ',', '.') }}
        </p>
    @endif

    <p>
        <strong>Negocio:</strong>
        {{ $oferta->company->name ?? 'Sin negocio' }}
    </p>

    @if($oferta->fecha_hasta)
        <p>
            <strong>Válida hasta:</strong>
            {{ $oferta->fecha_hasta->format('d/m/Y') }}
        </p>
    @endif

    @if($oferta->descripcion)
        <p>{{ $oferta->descripcion }}</p>
    @endif

    <p>Ingresá a Club Comofra para ver el detalle de la oferta.</p>
</body>
</html>
