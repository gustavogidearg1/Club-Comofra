<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Models\Localidad;
use App\Models\Provincia;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin_sitio|admin_empresa');
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $editId = $request->query('edit');
        $localidadEdit = $editId ? Localidad::with(['provincia.pais'])->find($editId) : null;

        // Para el combo del form
        $provincias = Provincia::with('pais')->orderBy('nombre')->get();

        $localidades = Localidad::query()
            ->with(['provincia.pais'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('cp', 'like', "%{$q}%")
                        ->orWhereHas('provincia', fn($p) => $p->where('nombre', 'like', "%{$q}%"))
                        ->orWhereHas('provincia.pais', fn($pa) => $pa->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('abm.localidades.index', compact('localidades', 'q', 'provincias', 'localidadEdit'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provincia_id' => ['required', 'integer', 'exists:provincias,id'],
            'nombre'       => ['required', 'string', 'max:120'],
            'cp'           => ['nullable', 'string', 'max:12'],
        ]);

        Localidad::create([
            'provincia_id' => $data['provincia_id'],
            'nombre'       => trim($data['nombre']),
            'cp'           => isset($data['cp']) ? trim($data['cp']) : null,
        ]);

        return redirect()
            ->route('abm.localidades.index')
            ->with('success', 'Localidad creada correctamente.');
    }

    public function update(Request $request, Localidad $localidad)
    {
        $data = $request->validate([
            'provincia_id' => ['required', 'integer', 'exists:provincias,id'],
            'nombre'       => ['required', 'string', 'max:120'],
            'cp'           => ['nullable', 'string', 'max:12'],
        ]);

        $localidad->update([
            'provincia_id' => $data['provincia_id'],
            'nombre'       => trim($data['nombre']),
            'cp'           => isset($data['cp']) ? trim($data['cp']) : null,
        ]);

        return redirect()
            ->route('abm.localidades.index')
            ->with('success', 'Localidad actualizada correctamente.');
    }

    public function destroy(Localidad $localidad)
    {
        try {
            $localidad->delete();

            return redirect()
                ->route('abm.localidades.index')
                ->with('success', 'Localidad eliminada correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('abm.localidades.index')
                ->with('error', 'No se puede eliminar: la localidad está asociada a otros registros.');
        }
    }
}
