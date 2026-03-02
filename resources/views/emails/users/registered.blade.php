@component('mail::message')
# ¡Hola {{ $user->name }}!

Tu usuario fue creado correctamente en **Club Comofra**.

@component('mail::panel')
**Datos de tu cuenta**
- **Nombre:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **Empresa:** {{ optional($user->company)->name ?? '—' }}
@endcomponent

@if(!empty($user->cuil))
**CUIL:** {{ $user->cuil }}
@endif

Si no reconocés este registro, por favor contactá al administrador de tu empresa.

Saludos,
**Equipo Club Comofra**
@endcomponent
