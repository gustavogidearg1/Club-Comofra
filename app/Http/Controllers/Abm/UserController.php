<?php

namespace App\Http\Controllers\Abm;

use App\Http\Controllers\Controller;
use App\Mail\UserRegisteredMail;
use App\Models\Company;
use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['role:admin_sitio|admin_empresa|rrhh']);
    }

    private function isSiteAdmin(): bool
    {
        return auth()->user()?->hasRole('admin_sitio') ?? false;
    }

    private function isCompanyAdmin(): bool
    {
        return auth()->user()?->hasRole('admin_empresa') ?? false;
    }

    private function ensureSameCompany(User $target): void
    {
        if ($this->isCompanyAdmin() && !$this->isSiteAdmin()) {
            abort_unless(
                (int) $target->company_id === (int) auth()->user()->company_id,
                403,
                'No tiene permisos para acceder a usuarios de otra empresa.'
            );
        }
    }

    private function getAvailableSectors(?int $companyId = null)
    {
        $companyId ??= auth()->user()->company_id;

        return Sector::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    private function normalizeAndSecureRequest(Request $request): void
    {
        if ($this->isCompanyAdmin() && !$this->isSiteAdmin()) {
            $request->merge([
                'company_id' => auth()->user()->company_id,
            ]);
        }

        $request->merge([
            'cuil' => preg_replace(
                '/\D+/',
                '',
                (string) $request->input('cuil')
            ),

            'telefono' => $request->filled('telefono')
                ? preg_replace(
                    '/[^\d+]/',
                    '',
                    (string) $request->input('telefono')
                )
                : null,
        ]);
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $companyId = $request->input('company_id');
        $roleName = $request->input('role');

        $activo = $request->has('activo')
            ? $request->input('activo')
            : '1';

        $perPage = (int) $request->input('per', 15);

        if ($this->isCompanyAdmin() && !$this->isSiteAdmin()) {
            $companyId = auth()->user()->company_id;
        }

        $companies = Company::query()
            ->when(
                $this->isCompanyAdmin() && !$this->isSiteAdmin(),
                fn ($query) => $query->whereKey($companyId)
            )
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->with(['roles', 'company', 'sectors'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('cuil', 'like', "%{$q}%");
                });
            })
            ->when(
                $companyId,
                fn ($query) => $query->where('company_id', $companyId)
            )
            ->when($roleName, function ($query) use ($roleName) {
                $query->whereHas(
                    'roles',
                    fn ($roleQuery) => $roleQuery->where(
                        'name',
                        $roleName
                    )
                );
            })
            ->when(
                $activo !== '',
                fn ($query) => $query->where('activo', $activo)
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('abm.users.index', compact(
            'users',
            'q',
            'companies',
            'roles',
            'companyId',
            'roleName',
            'activo',
            'perPage'
        ));
    }

    public function create()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name');

        $companies = Company::query()
            ->when(
                $this->isCompanyAdmin() && !$this->isSiteAdmin(),
                fn ($query) => $query->whereKey(
                    auth()->user()->company_id
                )
            )
            ->orderBy('name')
            ->get();

        $paises = Pais::query()
            ->orderBy('nombre')
            ->get();

        $provincias = Provincia::query()
            ->orderBy('nombre')
            ->get();

        $localidades = Localidad::query()
            ->orderBy('nombre')
            ->get();

        $sectors = $this->getAvailableSectors();

        return view('abm.users.create', compact(
            'roles',
            'companies',
            'paises',
            'provincias',
            'localidades',
            'sectors'
        ));
    }

    public function store(Request $request)
    {
        $this->normalizeAndSecureRequest($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:180',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'max:255',
                'confirmed',
            ],

            'cuil' => [
                'required',
                'string',
                Rule::unique('users', 'cuil')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $request->input('company_id')
                    )),
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'pais_id' => [
                'nullable',
                'integer',
                'exists:paises,id',
            ],

            'provincia_id' => [
                'nullable',
                'integer',
                'exists:provincias,id',
            ],

            'localidad_id' => [
                'nullable',
                'integer',
                'exists:localidades,id',
            ],

            'fecha_nacimiento' => [
                'nullable',
                'date',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],

            'imagen' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:30',
            ],

            'roles' => [
                'nullable',
                'array',
            ],

            'roles.*' => [
                'string',
                'exists:roles,name',
            ],

            'sectors' => [
                'nullable',
                'array',
            ],

            'sectors.*' => [
                'integer',
                Rule::exists('sectors', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $request->input('company_id')
                    )),
            ],
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request
                ->file('imagen')
                ->store('users', 'public');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'cuil' => $data['cuil'],
            'direccion' => $data['direccion'] ?? null,
            'company_id' => $data['company_id'],
            'pais_id' => $data['pais_id'] ?? null,
            'provincia_id' => $data['provincia_id'] ?? null,
            'localidad_id' => $data['localidad_id'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'activo' => array_key_exists('activo', $data)
                ? (bool) $data['activo']
                : true,
            'imagen' => $data['imagen'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->sectors()->sync($data['sectors'] ?? []);

        $user->load(['company', 'sectors']);

        Mail::to($user->email)
            ->send(new UserRegisteredMail($user));

        return redirect()
            ->route('abm.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user)
    {
        $this->ensureSameCompany($user);

        $user->load([
            'roles',
            'company',
            'pais',
            'provincia',
            'localidad',
            'sectors',
        ]);

        return view('abm.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->ensureSameCompany($user);

        $companies = Company::query()
            ->when(
                $this->isCompanyAdmin() && !$this->isSiteAdmin(),
                fn ($query) => $query->whereKey(
                    auth()->user()->company_id
                )
            )
            ->orderBy('name')
            ->get();

        $paises = Pais::query()
            ->orderBy('nombre')
            ->get();

        $provincias = Provincia::query()
            ->orderBy('nombre')
            ->get();

        $localidades = Localidad::query()
            ->orderBy('nombre')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name');

        $currentRoleNames = $user
            ->roles
            ->pluck('name')
            ->all();

        $sectors = $this->getAvailableSectors($user->company_id);

        $currentSectorIds = $user
            ->sectors()
            ->pluck('sectors.id')
            ->all();

        return view('abm.users.edit', compact(
            'user',
            'companies',
            'paises',
            'provincias',
            'localidades',
            'roles',
            'currentRoleNames',
            'sectors',
            'currentSectorIds'
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSameCompany($user);

        $this->normalizeAndSecureRequest($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'max:255',
                'confirmed',
            ],

            'cuil' => [
                'required',
                'string',
                Rule::unique('users', 'cuil')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $request->input('company_id')
                    ))
                    ->ignore($user->id),
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'pais_id' => [
                'nullable',
                'integer',
                'exists:paises,id',
            ],

            'provincia_id' => [
                'nullable',
                'integer',
                'exists:provincias,id',
            ],

            'localidad_id' => [
                'nullable',
                'integer',
                'exists:localidades,id',
            ],

            'fecha_nacimiento' => [
                'nullable',
                'date',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],

            'imagen' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:30',
            ],

            'roles' => [
                'nullable',
                'array',
            ],

            'roles.*' => [
                'string',
                'exists:roles,name',
            ],

            'sectors' => [
                'nullable',
                'array',
            ],

            'sectors.*' => [
                'integer',
                Rule::exists('sectors', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $request->input('company_id')
                    )),
            ],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cuil' => $data['cuil'],
            'direccion' => $data['direccion'] ?? null,
            'company_id' => $data['company_id'],
            'pais_id' => $data['pais_id'] ?? null,
            'provincia_id' => $data['provincia_id'] ?? null,
            'localidad_id' => $data['localidad_id'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'activo' => array_key_exists('activo', $data)
                ? (bool) $data['activo']
                : (bool) $user->activo,
        ];

        if ($request->hasFile('imagen')) {
            if ($user->imagen) {
                Storage::disk('public')->delete($user->imagen);
            }

            $updateData['imagen'] = $request
                ->file('imagen')
                ->store('users', 'public');
        }

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make(
                $data['password']
            );
        }

        $user->update($updateData);

        $user->syncRoles($data['roles'] ?? []);
        $user->sectors()->sync($data['sectors'] ?? []);

        return redirect()
            ->route('abm.users.show', $user)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function export(Request $request): StreamedResponse
    {
        $q = $request->string('q')->toString();
        $companyId = $request->input('company_id');
        $roleName = $request->input('role');
        $activo = $request->input('activo', '1');

        if ($this->isCompanyAdmin() && !$this->isSiteAdmin()) {
            $companyId = auth()->user()->company_id;
        }

        $users = User::query()
            ->with(['roles', 'company', 'sectors'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('cuil', 'like', "%{$q}%");
                });
            })
            ->when(
                $companyId,
                fn ($query) => $query->where(
                    'company_id',
                    $companyId
                )
            )
            ->when($roleName, function ($query) use ($roleName) {
                $query->whereHas(
                    'roles',
                    fn ($roleQuery) => $roleQuery->where(
                        'name',
                        $roleName
                    )
                );
            })
            ->when(
                $activo !== '',
                fn ($query) => $query->where('activo', $activo)
            )
            ->orderBy('name');

        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Nombre',
                'Email',
                'Telefono',
                'Empresa',
                'CUIL',
                'Roles',
                'Sectores',
                'Activo',
            ], ';');

            $users->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $user) {
                    fputcsv($out, [
                        $user->name,
                        $user->email,
                        $user->telefono,
                        $user->company?->name,
                        $user->cuil,
                        $user->roles
                            ->pluck('name')
                            ->join(', '),
                        $user->sectors
                            ->pluck('name')
                            ->join(', '),
                        $user->activo ? 'Si' : 'No',
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
