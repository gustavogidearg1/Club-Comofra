<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin_sitio|admin_empresa');
    }

    public function index()
    {
        $sectors = Sector::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->paginate(15);

        return view('abm.sectors.index', compact('sectors'));
    }

    public function create()
    {
        return view('abm.sectors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sectors')->where(
                    fn ($query) => $query->where(
                        'company_id',
                        Auth::user()->company_id
                    )
                ),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'calidad' => ['nullable', 'boolean'],
        ]);

        $data['company_id'] = Auth::user()->company_id;
        $data['active'] = true;
        $data['calidad'] = $request->boolean('calidad');

        Sector::create($data);

        return redirect()
            ->route('abm.sectors.index')
            ->with('success', 'Sector creado correctamente.');
    }

    public function edit(Sector $sector)
    {
        abort_unless(
            $sector->company_id === Auth::user()->company_id,
            403
        );

        return view('abm.sectors.edit', compact('sector'));
    }

    public function update(Request $request, Sector $sector)
    {
        abort_unless(
            $sector->company_id === Auth::user()->company_id,
            403
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'calidad' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['calidad'] = $request->boolean('calidad');
        $data['active'] = $request->boolean('active');

        $sector->update($data);

        return redirect()
            ->route('abm.sectors.index')
            ->with('success', 'Sector actualizado correctamente.');
    }

    public function destroy(Sector $sector)
    {
        abort_unless(
            $sector->company_id === Auth::user()->company_id,
            403
        );

        // Baja lógica para no perder información histórica.
        $sector->update(['active' => false]);

        return redirect()
            ->route('abm.sectors.index')
            ->with('success', 'Sector desactivado correctamente.');
    }
}
