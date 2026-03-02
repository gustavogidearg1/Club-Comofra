<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Models\PointReference;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PointReferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin_sitio|admin_empresa']);
    }

    public function index(Request $r)
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId = $isSiteAdmin ? null : ($u->company_id ?? null);

        $q = PointReference::query();

        if (!$isSiteAdmin) {
            $q->forCompany($companyId);
        }

        if ($term = trim((string)$r->get('q'))) {
            $q->where('name', 'like', "%{$term}%");
        }

        $rows = $q->orderBy('sort_order')
                  ->orderBy('name')
                  ->paginate(20)
                  ->withQueryString();

        return view('abm.point_references.index', [
            'rows' => $rows,
            'q' => $term ?? '',
        ]);
    }

    public function create()
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');



        return view('abm.point_references.create', [
            'row' => new PointReference(),
            'companies' => $isSiteAdmin ? Company::orderBy('name')->get() : collect(),
            'isSiteAdmin' => $isSiteAdmin,
        ]);
    }

    public function store(Request $r)
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId = $isSiteAdmin ? null : ($u->company_id ?? null);

        $data = $r->validate([
            'company_id' => ['nullable','integer','exists:companies,id'],
            'name' => [
                'required','string','max:120',
                Rule::unique('point_references','name')
                    ->where(fn($q) => $q->where('company_id', $isSiteAdmin ? $r->company_id : $companyId)),
            ],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:0','max:65535'],
        ]);

        if (!$isSiteAdmin) {
            $data['company_id'] = $companyId;
        }

        $data['created_by_user_id'] = $u->id;
        $data['updated_by_user_id'] = $u->id;
        $data['is_active'] = (bool)$r->boolean('is_active');
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        PointReference::create($data);

        return redirect()->route('abm.point-references.index')->with('ok', 'Referencia creada.');
    }

    public function edit(PointReference $point_reference)
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');

        if (!$isSiteAdmin) {
            $allowed = ($point_reference->company_id === null) || ($point_reference->company_id == ($u->company_id ?? null));
            abort_unless($allowed, 403);
        }

        return view('abm.point_references.edit', [
            'row' => $point_reference,
            'companies' => $isSiteAdmin ? Company::orderBy('name')->get() : collect(),
            'isSiteAdmin' => $isSiteAdmin,
        ]);
    }

    public function update(Request $r, PointReference $point_reference)
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId = $isSiteAdmin ? null : ($u->company_id ?? null);

        if (!$isSiteAdmin) {
            $allowed = ($point_reference->company_id === null) || ($point_reference->company_id == $companyId);
            abort_unless($allowed, 403);
        }

        $targetCompany = $isSiteAdmin ? $r->company_id : $point_reference->company_id;

        $data = $r->validate([
            'company_id' => ['nullable','integer','exists:companies,id'],
            'name' => [
                'required','string','max:120',
                Rule::unique('point_references','name')
                    ->where(fn($q) => $q->where('company_id', $isSiteAdmin ? $targetCompany : $companyId))
                    ->ignore($point_reference->id),
            ],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:0','max:65535'],
        ]);

        if (!$isSiteAdmin) {
            unset($data['company_id']);
        }

        $data['updated_by_user_id'] = $u->id;
        $data['is_active'] = (bool)$r->boolean('is_active');
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        $point_reference->update($data);

        return redirect()->route('abm.point-references.index')->with('ok', 'Referencia actualizada.');
    }

    public function destroy(PointReference $point_reference)
    {
        $u = Auth::user();
        $isSiteAdmin = $u->hasRole('admin_sitio');

        if (!$isSiteAdmin) {
            $allowed = ($point_reference->company_id === null) || ($point_reference->company_id == ($u->company_id ?? null));
            abort_unless($allowed, 403);
        }

        $point_reference->delete();

        return redirect()->route('abm.point-references.index')->with('ok', 'Referencia eliminada.');
    }

  public function show(PointReference $point_reference)
{
    $u = Auth::user();
    $isSiteAdmin = $u->hasRole('admin_sitio');

    if (!$isSiteAdmin) {
        $allowed = ($point_reference->company_id === null) || ($point_reference->company_id == ($u->company_id ?? null));
        abort_unless($allowed, 403);
    }

    return view('abm.point_references.show', [
        'row' => $point_reference,
    ]);
}

}
