<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

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

        // 🔐 Lógica por roles
        if ($user?->hasRole('admin_sitio')) {
            // ve todo
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

        $ofertasDestacadas = $ofertas
            ->where('destacada', true)
            ->values();

        return view('dashboard', compact('ofertas', 'ofertasDestacadas'));
    }
}
