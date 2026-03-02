<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin_sitio|admin_empresa');
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $editId = $request->query('edit');
        $provinciaEdit = $editId ? Provincia::with('pais')->find($editId) : null;

        $paises = Pais::orderBy('nombre')->get();

        $provincias = Provincia::query()
            ->with('pais')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhereHas('pais', fn ($p) => $p->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('abm.provincias.index', compact('provincias', 'q', 'paises', 'provinciaEdit'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pais_id' => ['required', 'integer', 'exists:paises,id'],
            'nombre'  => ['required', 'string', 'max:100'],
        ]);

        Provincia::create([
            'pais_id' => $data['pais_id'],
            'nombre'  => trim($data['nombre']),
        ]);

        return redirect()
            ->route('abm.provincias.index')
            ->with('success', 'Provincia creada correctamente.');
    }

    public function update(Request $request, Provincia $provincia)
    {
        $data = $request->validate([
            'pais_id' => ['required', 'integer', 'exists:paises,id'],
            'nombre'  => ['required', 'string', 'max:100'],
        ]);

        $provincia->update([
            'pais_id' => $data['pais_id'],
            'nombre'  => trim($data['nombre']),
        ]);

        return redirect()
            ->route('abm.provincias.index')
            ->with('success', 'Provincia actualizada correctamente.');
    }

    public function destroy(Provincia $provincia)
    {
        try {
            $provincia->delete();

            return redirect()
                ->route('abm.provincias.index')
                ->with('success', 'Provincia eliminada correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('abm.provincias.index')
                ->with('error', 'No se puede eliminar: la provincia está asociada a otros registros.');
        }
    }
}
