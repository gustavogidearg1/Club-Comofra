<?php

namespace App\Http\Controllers;

use App\Models\PointMovement;
use App\Models\PointRedemption;
use App\Models\User;
use App\Notifications\MovimientoPuntosCreado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RedemptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

private function saldoEmpleado(int $employeeUserId): float
{
    return round(
        (float) PointMovement::where('employee_user_id', $employeeUserId)->sum('points'),
        2
    );
}

    private function sameCompanyIfApplies(?int $companyA, ?int $companyB): bool
    {
        if (empty($companyA) || empty($companyB)) return true;
        return (int) $companyA === (int) $companyB;
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->hasRole('negocio') && !$user->hasRole('admin_sitio')) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para crear consumos.');
        }

        $companyId = $user->company_id;

        $employees = User::query()
            ->when(
                !$user->hasRole('admin_sitio') && !empty($companyId),
                fn($q) => $q->where('company_id', $companyId)
            )
            ->where('activo', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'empleado'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'cuil', 'company_id']);

        return view('redeems.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $auth = Auth::user();

        if (!$auth->hasRole('negocio') && !$auth->hasRole('admin_sitio')) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para crear consumos.');
        }

        $data = $request->validate([
            'employee_user_id' => ['required', 'integer', 'exists:users,id'],
            'points'           => ['required', 'integer', 'min:1'],
            'note'             => ['nullable', 'string', 'max:500'],
            'business_id'      => ['nullable', 'integer', 'exists:users,id'],
        ], [], [
            'employee_user_id' => 'empleado',
            'points'           => 'puntos',
            'note'             => 'nota',
            'business_id'      => 'negocio',
        ]);

        $employee = User::findOrFail($data['employee_user_id']);

        $business = $auth;

        if ($auth->hasRole('admin_sitio') && !empty($data['business_id'])) {
            $business = User::findOrFail($data['business_id']);

            if (!$business->hasRole('negocio')) {
                return back()
                    ->withErrors(['business_id' => 'El usuario seleccionado no es un negocio válido.'])
                    ->withInput();
            }
        }

        if (!$this->sameCompanyIfApplies($employee->company_id, $business->company_id)) {
            return back()
                ->withErrors(['employee_user_id' => 'El empleado no pertenece a la misma empresa que el negocio.'])
                ->withInput();
        }

        $pointsToRedeem = round((float) $data['points'], 2);
        $token = Str::random(48);

        $result = DB::transaction(function () use ($auth, $business, $employee, $pointsToRedeem, $data, $token) {
            $saldo = $this->saldoEmpleado((int) $employee->id);

            if ($saldo < $pointsToRedeem) {
                return ['ok' => false, 'msg' => 'Saldo insuficiente para consumir esos puntos.'];
            }

            $movement = PointMovement::create([
                'company_id'        => $business->company_id ?? $employee->company_id,
                'employee_user_id'  => $employee->id,
                'business_user_id'  => $business->id,
                'created_by'        => $auth->id,
                'confirmed_by'      => $auth->id,
                'batch_id'          => null,
                'type'              => 'redeem',
                'subtype'           => 'business_spend',
                'points'            => -abs($pointsToRedeem),
                'money_amount'      => null,
                'reference'         => 'GASTO_NEGOCIO',
                'note'              => $data['note'] ?? null,
                'occurred_at'       => now(),
            ]);

            $redemption = PointRedemption::create([
                'company_id'        => $movement->company_id,
                'employee_user_id'  => $employee->id,
                'business_user_id'  => $business->id,
                'created_by'        => $auth->id,
                'point_movement_id' => $movement->id,
                'points'            => $pointsToRedeem,
                'reference'         => 'GASTO_NEGOCIO',
                'note'              => $data['note'] ?? null,
                'status'            => 'confirmed',
                'token'             => $token,
                'expires_at'        => null,
                'confirmed_at'      => now(),
                'confirmed_by'      => $auth->id,
            ]);

            return ['ok' => true, 'movement' => $movement, 'redemption' => $redemption];
        });

        if (!$result['ok']) {
            return back()->withErrors(['points' => $result['msg']])->withInput();
        }

        $redemption = $result['redemption'];
        $url = route('redeems.confirm.show', $redemption->token);

        try {
            $mov = $result['movement']->load(['employee', 'business']);

            if (!empty($mov->employee?->email)) {
                $mov->employee->notify(new MovimientoPuntosCreado($mov));
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificaciones de consumo (store)', [
                'movement_id'   => $result['movement']->id ?? null,
                'redemption_id' => $redemption->id ?? null,
                'error'         => $e->getMessage(),
            ]);
        }

        return view('redeems.created', [
            'redemption' => $redemption,
            'employee'   => $employee,
            'business'   => $business,
            'url'        => $url,
        ]);
    }

    public function showConfirm(string $token)
    {
        $user = Auth::user();

        if (
            !$user->hasRole('empleado') &&
            !$user->hasRole('negocio') &&
            !$user->hasRole('admin_sitio')
        ) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para ver este comprobante.');
        }

        $redemption = PointRedemption::with([
            'business:id,name,email,company_id',
            'employee:id,name,email,cuil,company_id',
            'company:id,name',
            'movement:id,points,occurred_at',
        ])->where('token', $token)->firstOrFail();

        if (!$user->hasRole('admin_sitio')) {
            $isEmployee = (int) $redemption->employee_user_id === (int) $user->id;
            $isBusiness = (int) $redemption->business_user_id === (int) $user->id;

            if (!$isEmployee && !$isBusiness) {
                return redirect()->route('dashboard')->with('error', 'Este comprobante no corresponde a su usuario.');
            }
        }

        return view('redeems.confirm', [
            'redemption' => $redemption,
        ]);
    }

    public function manualIndex()
    {
        $user = Auth::user();

        if (
    !$user->hasRole('empleado')
    && !$user->hasRole('rrhh')
    && !$user->hasRole('admin_sitio')
) {
            return redirect()->route('dashboard')->with('error', 'Solo un empleado puede cargar un consumo manual.');
        }

        $businesses = User::query()
            ->where('activo', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'negocio'))
            ->when(!empty($user->company_id), fn($q) => $q->where('company_id', $user->company_id))
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        $saldo = $this->saldoEmpleado((int) $user->id);

        return view('redeems.manual-index', [
            'employee'   => $user,
            'businesses' => $businesses,
            'saldo'      => $saldo,
        ]);
    }

    public function manualCreate(User $business)
    {
        $user = Auth::user();

        if (
    !$user->hasRole('empleado')
    && !$user->hasRole('rrhh')
    && !$user->hasRole('admin_sitio')
) {
            return redirect()->route('dashboard')->with('error', 'Solo un empleado puede cargar un consumo manual.');
        }

        if (!$business->hasRole('negocio') && !$business->hasRole('admin_sitio')) {
            return redirect()->route('dashboard')->with('error', 'El QR no corresponde a un negocio válido.');
        }

        if (!$this->sameCompanyIfApplies($user->company_id, $business->company_id)) {
            return redirect()->route('dashboard')->with('error', 'Este negocio no pertenece a tu empresa.');
        }

        $saldo = $this->saldoEmpleado((int) $user->id);

        return view('redeems.manual-create', [
            'business' => $business,
            'employee' => $user,
            'saldo'    => $saldo,
        ]);
    }

    public function manualStore(Request $request, User $business)
    {
        $user = Auth::user();

        if (
    !$user->hasRole('empleado')
    && !$user->hasRole('rrhh')
    && !$user->hasRole('admin_sitio')
) {
            return redirect()->route('dashboard')->with('error', 'Solo un empleado puede cargar un consumo manual.');
        }

        if (!$business->hasRole('negocio') && !$business->hasRole('admin_sitio')) {
            return back()->with('error', 'Negocio inválido.');
        }

        if (!$this->sameCompanyIfApplies($user->company_id, $business->company_id)) {
            return back()->with('error', 'Este negocio no pertenece a tu empresa.');
        }

        $data = $request->validate([
            'points' => ['required', 'numeric', 'min:0.01'],
            'note'   => ['nullable', 'string', 'max:500'],
        ], [], [
            'points' => 'puntos',
            'note'   => 'nota',
        ]);

        $pointsToRedeem = round((float) $data['points'], 2);

        $result = DB::transaction(function () use ($user, $business, $pointsToRedeem, $data) {
            $saldo = $this->saldoEmpleado((int) $user->id);

            if ($saldo < $pointsToRedeem) {
                return ['ok' => false, 'msg' => 'Saldo insuficiente para consumir esos puntos.'];
            }

            $movement = PointMovement::create([
                'company_id'        => $user->company_id ?? $business->company_id,
                'employee_user_id'  => $user->id,
                'business_user_id'  => $business->id,
                'created_by'        => $user->id,
                'confirmed_by'      => $user->id,
                'type'              => 'redeem',
                'subtype'           => 'manual_qr',
                'points'            => -abs($pointsToRedeem),
                'reference'         => 'GASTO_MANUAL_QR',
                'note'              => $data['note'] ?? null,
                'occurred_at'       => now(),
            ]);

            $redemption = PointRedemption::create([
                'company_id'        => $movement->company_id,
                'employee_user_id'  => $user->id,
                'business_user_id'  => $business->id,
                'created_by'        => $user->id,
                'point_movement_id' => $movement->id,
                'points'            => $pointsToRedeem,
                'reference'         => 'GASTO_MANUAL_QR',
                'note'              => $data['note'] ?? null,
                'status'            => 'confirmed',
                'token'             => Str::random(48),
                'confirmed_at'      => now(),
                'confirmed_by'      => $user->id,
            ]);

            return ['ok' => true, 'movement' => $movement, 'redemption' => $redemption];
        });

        if (!$result['ok']) {
            return back()->withErrors(['points' => $result['msg']])->withInput();
        }

        try {
            $mov = $result['movement']->load(['employee', 'business']);

            if (!empty($mov->employee?->email)) {
                $mov->employee->notify(new MovimientoPuntosCreado($mov));
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificaciones de consumo manual', [
                'movement_id'   => $result['movement']->id ?? null,
                'redemption_id' => $result['redemption']->id ?? null,
                'error'         => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('redeems.confirm.show', $result['redemption']->token)
            ->with('success', 'Consumo registrado correctamente.');
    }

    public function businessJson(int $id)
    {
        $auth = Auth::user();

        $u = User::query()->whereKey($id)->firstOrFail();

        abort_unless($u->hasRole('negocio'), 404);

        if (!$auth->hasRole('admin_sitio')) {
            abort_unless($this->sameCompanyIfApplies($auth->company_id, $u->company_id), 404);
        }

        return response()->json([
            'id'   => $u->id,
            'name' => $u->name,
        ]);
    }
}
