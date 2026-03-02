@component('mail::message')
# ¡Hola {{ $user->name }}!

Se registró un movimiento de puntos en tu cuenta por una **Carga masiva**.

@component('mail::panel')
**Detalle**
- **Puntos:** {{ $pointsDelta > 0 ? '+' : '' }}{{ $pointsDelta }}
@if(!empty($reference))
- **Referencia:** {{ $reference }}
@endif
@endcomponent

Podés ver el detalle ingresando a Club Comofra.

Saludos,
**Equipo Club Comofra**
@endcomponent
