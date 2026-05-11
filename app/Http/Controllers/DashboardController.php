<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $activeCompany = $user->company;

    // OFERTAS (lo que ya tenés)
    $ofertasQuery = Oferta::with(['imagenes', 'company', 'user'])
        ->where('publicada', true)
        ->where('estado', 'publicada')
        ->where(function ($q) {
            $q->whereNull('fecha_desde')
              ->orWhereDate('fecha_desde', '<=', now()->toDateString());
        })
        ->where(function ($q) {
            $q->whereNull('fecha_hasta')
              ->orWhereDate('fecha_hasta', '>=', now()->toDateString());
        });

    if ($user?->hasRole('admin_sitio')) {
        // todo
    } elseif ($user?->hasRole('admin_empresa') || $user?->hasRole('empleado')) {
        $ofertasQuery->where('company_id', $user->company_id);
    } elseif ($user?->hasRole('negocio')) {
        $ofertasQuery->where('user_id', $user->id);
    } else {
        $ofertasQuery->whereRaw('1 = 0');
    }

    $ofertas = $ofertasQuery
        ->orderByDesc('destacada')
        ->orderBy('orden')
        ->orderByDesc('id')
        ->get();

    $ofertasDestacadas = $ofertas->where('destacada', true)->values();

    // 👇 NUEVO: traer negocios
$negocios = User::with(['company', 'localidad', 'provincia', 'pais'])
    ->whereHas('roles', fn($q) => $q->where('name', 'negocio'))
    ->where('activo', 1)
    ->when(!$user->hasRole('admin_sitio'), function ($q) use ($user) {
        $q->where('company_id', $user->company_id);
    })
    ->orderBy('name', 'asc')
    ->get();

    // 👇 Cumpleaños de hoy
$cumplesHoy = User::whereNotNull('fecha_nacimiento')
    ->whereMonth('fecha_nacimiento', Carbon::now()->month)
    ->whereDay('fecha_nacimiento', Carbon::now()->day)
    ->where('activo', 1)
    ->get();

    return view('dashboard', compact(
    'ofertas',
    'ofertasDestacadas',
    'negocios',
    'cumplesHoy',
    'activeCompany'
));
}
}
