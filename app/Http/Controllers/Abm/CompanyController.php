<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin_sitio']);
    }

    public function index()
    {
        $rows = Company::query()
            ->orderBy('name')
            ->paginate(15);

        return view('abm.companies.index', compact('rows'));
    }

    public function create()
    {
        return view('abm.companies.create');
    }

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        Company::create($data);

        return redirect()->route('abm.companies.index')->with('success', 'Compañía creada.');
    }

    public function edit(Company $company)
    {
        return view('abm.companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($data);

        return redirect()->route('abm.companies.index')->with('success', 'Compañía actualizada.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return back()->with('success', 'Compañía eliminada.');
    }

    public function show(Company $company)
{
    return view('abm.companies.show', compact('company'));
}
}
