<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    private function isSiteAdmin(): bool
    {
        $u = auth()->user();
        return $u && method_exists($u, 'hasRole') && $u->hasRole('admin_sitio');
    }

    public function index()
    {
        $u = auth()->user();

        $q = Empresa::query()->with('company')->orderBy('name');

        if (!$this->isSiteAdmin()) {
            $q->where('company_id', $u->company_id);
        }

        $rows = $q->paginate(15);

        return view('abm.empresas.index', compact('rows'));
    }

    public function create()
    {
        return view('abm.empresas.create');
    }

    public function store(StoreEmpresaRequest $request)
    {
        $u = auth()->user();

        $data = $request->validated();
        $data['company_id'] = $u->company_id; // 🔒 siempre por compañía del usuario (salvo que luego quieras permitir elegir)

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos_empresas', 'public');
        }

        Empresa::create($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa creada.');
    }

    public function edit(Empresa $empresa)
    {
        $this->authorizeEmpresa($empresa);
        return view('abm.empresas.edit', compact('empresa'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        $this->authorizeEmpresa($empresa);

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($empresa->logo) Storage::disk('public')->delete($empresa->logo);
            $data['logo'] = $request->file('logo')->store('logos_empresas', 'public');
        }

        $empresa->update($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada.');
    }

    public function destroy(Empresa $empresa)
    {
        $this->authorizeEmpresa($empresa);

        if ($empresa->logo) Storage::disk('public')->delete($empresa->logo);
        $empresa->delete();

        return back()->with('success', 'Empresa eliminada.');
    }

    private function authorizeEmpresa(Empresa $empresa): void
    {
        if ($this->isSiteAdmin()) return;

        $u = auth()->user();
        abort_if($empresa->company_id !== $u->company_id, 403, 'No autorizado.');
    }
}

