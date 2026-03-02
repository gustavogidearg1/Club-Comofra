<?php

namespace App\Http\Controllers;

use App\Models\PointRedemption;
use App\Models\PointSettlement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin_sitio|admin_empresa|negocio']);
    }

    /**
     * Index de consumos a rendir/facturar.
     * Default: pendiente (settlement_id null)
     */
    public function consumosIndex(Request $request)
    {
        $u = Auth::user();

        $businessId = $request->integer('negocio_id');
        $employeeId = $request->integer('empleado_id');
        $desde      = $request->input('desde');  // YYYY-MM-DD
        $hasta      = $request->input('hasta');  // YYYY-MM-DD
        $estado     = $request->string('estado')->toString() ?: 'pendiente';

        // Base query
        $q = PointRedemption::query()
            ->with([
                'employee:id,name,email,cuil,company_id',
                'business:id,name,email,company_id',
                'company:id,name',
                'movement:id,points,occurred_at',
            ])
            ->where('status', 'confirmed');

        // Seguridad por rol/empresa
        if ($u->hasRole('negocio')) {
            $q->where('business_user_id', $u->id);
            if (!empty($u->company_id)) $q->where('company_id', $u->company_id);
        }

        if ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
            if (!empty($u->company_id)) $q->where('company_id', $u->company_id);
        }

        // ✅ Admin puede filtrar por negocio
        $negocios = collect();
        if ($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa')) {
            $negocios = User::query()
                ->whereHas('roles', fn($r) => $r->where('name', 'negocio'))
                ->when(
                    !$u->hasRole('admin_sitio') && !empty($u->company_id),
                    fn($qq) => $qq->where('company_id', $u->company_id)
                )
                ->orderBy('name')
                ->get(['id','name']);

            if (!empty($businessId)) {
                $q->where('business_user_id', $businessId);
            }
        }

        // Estado
        if ($estado === 'pendiente') {
            $q->whereNull('settlement_id');
        } elseif ($estado === 'rendido') {
            $q->whereNotNull('settlement_id');
        }

        // Empleado
        if (!empty($employeeId)) {
            $q->where('employee_user_id', $employeeId);
        }

        // Fechas (occurred_at si existe, sino created_at)
        if (!empty($desde)) {
            $q->where(function ($w) use ($desde) {
                $w->whereHas('movement', fn($m) => $m->whereDate('occurred_at', '>=', $desde))
                  ->orWhereDate('created_at', '>=', $desde);
            });
        }

        if (!empty($hasta)) {
            $q->where(function ($w) use ($hasta) {
                $w->whereHas('movement', fn($m) => $m->whereDate('occurred_at', '<=', $hasta))
                  ->orWhereDate('created_at', '<=', $hasta);
            });
        }

        $consumos = $q->orderByDesc('id')->paginate(20)->withQueryString();

        $empleados = User::query()
            ->whereHas('roles', fn($r) => $r->where('name', 'empleado'))
            ->when(
                !$u->hasRole('admin_sitio') && !empty($u->company_id),
                fn($qq) => $qq->where('company_id', $u->company_id)
            )
            ->orderBy('name')
            ->get(['id','name','cuil','email','company_id']);

        $totalPuntos = (int) $consumos->getCollection()->sum('points');
        $totalPesos  = $totalPuntos;

        return view('redeems.rendiciones-empresa.index', [
            'consumos'    => $consumos,
            'empleados'   => $empleados,
            'negocios'    => $negocios,
            'totalPuntos' => $totalPuntos,
            'totalPesos'  => $totalPesos,
            'estado'      => $estado,
        ]);
    }

    /**
     * Crear rendición desde consumos seleccionados.
     */
    public function store(Request $request)
    {
        $u = Auth::user();

        $data = $request->validate([
            'redemption_ids'   => ['required', 'array', 'min:1'],
            'redemption_ids.*' => ['integer'],
            'period_from'      => ['nullable', 'date'],
            'period_to'        => ['nullable', 'date'],
            'note'             => ['nullable', 'string', 'max:500'],
        ], [], [
            'redemption_ids' => 'consumos seleccionados',
            'period_from'    => 'desde',
            'period_to'      => 'hasta',
            'note'           => 'nota',
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['redemption_ids'])));

        $settlement = DB::transaction(function () use ($u, $ids, $data) {

            $rows = PointRedemption::query()
                ->with(['business:id,company_id', 'company:id,name'])
                ->whereIn('id', $ids)
                ->where('status', 'confirmed')
                ->whereNull('settlement_id')
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                abort(422, 'No hay consumos válidos para rendir.');
            }

            // Seguridad por rol
            if ($u->hasRole('negocio')) {
                if ($rows->contains(fn($r) => (int)$r->business_user_id !== (int)$u->id)) {
                    abort(403, 'No podés rendir consumos de otro negocio.');
                }
            } elseif ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
                if (!empty($u->company_id) && $rows->contains(fn($r) => (int)$r->company_id !== (int)$u->company_id)) {
                    abort(403, 'No podés rendir consumos de otra empresa.');
                }
            }

            // Regla: misma empresa y mismo negocio
            $companyId  = (int) $rows->first()->company_id;
            $businessId = (int) $rows->first()->business_user_id;

            if ($rows->contains(fn($r) => (int)$r->company_id !== $companyId)) {
                abort(422, 'Los consumos seleccionados pertenecen a distintas empresas. Hacé rendiciones separadas.');
            }
            if ($rows->contains(fn($r) => (int)$r->business_user_id !== $businessId)) {
                abort(422, 'Los consumos seleccionados pertenecen a distintos negocios. Hacé rendiciones separadas.');
            }

            $totalPoints = (int) $rows->sum('points');

            $settlement = PointSettlement::create([
                'company_id'       => $companyId,
                'business_user_id' => $businessId,
                'period_from'      => $data['period_from'] ?? null,
                'period_to'        => $data['period_to'] ?? null,
                'total_points'     => $totalPoints,
                'total_amount'     => $totalPoints,
                'status'           => 'draft',
                'note'             => $data['note'] ?? null,
            ]);

            PointRedemption::whereIn('id', $rows->pluck('id'))
                ->update(['settlement_id' => $settlement->id]);

            return $settlement;
        });

        return redirect()
            ->route('redeems.rendiciones_empresa.show', $settlement->id)
            ->with('success', 'Rendición creada correctamente.');
    }

    /**
     * ✅ SHOW (este es el que te está faltando)
     */
    public function show(PointSettlement $settlement)
    {
        $u = Auth::user();

        $settlement->load([
            'company:id,name',
            'business:id,name,email,company_id',
            'invoicedBy:id,name',
            'redemptions' => function ($q) {
                $q->with(['employee:id,name,cuil', 'movement:id,occurred_at'])
                  ->orderByDesc('id');
            },
        ]);

        // Seguridad por rol/empresa
        if ($u->hasRole('negocio')) {
            abort_unless((int)$settlement->business_user_id === (int)$u->id, 403);
            if (!empty($u->company_id)) abort_unless((int)$settlement->company_id === (int)$u->company_id, 403);
        }

        if ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
            if (!empty($u->company_id)) abort_unless((int)$settlement->company_id === (int)$u->company_id, 403);
        }

        return view('redeems.rendiciones-empresa.show', compact('settlement'));
    }

    public function markInvoiced(Request $request, PointSettlement $settlement)
    {
        $u = Auth::user();

        if ($u->hasRole('negocio')) {
            abort_unless((int)$settlement->business_user_id === (int)$u->id, 403);
        }
        if ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
            if (!empty($u->company_id)) abort_unless((int)$settlement->company_id === (int)$u->company_id, 403);
        }

        $data = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:60'],
        ]);

        if ($settlement->status === 'invoiced') {
            return back()->with('info', 'Esta rendición ya está marcada como facturada.');
        }

        $settlement->update([
            'status'         => 'invoiced',
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoiced_at'    => now(),
            'invoiced_by'    => $u->id,
        ]);

        return back()->with('success', 'Rendición marcada como facturada.');
    }

    public function settlementsIndex(Request $request)
    {
        $u = Auth::user();
        $estado = $request->string('estado')->toString() ?: 'todas'; // draft|invoiced|todas

        $q = PointSettlement::query()
            ->with(['company:id,name', 'business:id,name,company_id', 'invoicedBy:id,name']);

        if ($u->hasRole('negocio')) {
            $q->where('business_user_id', $u->id);
            if (!empty($u->company_id)) $q->where('company_id', $u->company_id);
        }

        if ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
            if (!empty($u->company_id)) $q->where('company_id', $u->company_id);
        }

        if ($estado === 'draft') {
            $q->where('status', 'draft');
        } elseif ($estado === 'invoiced') {
            $q->where('status', 'invoiced');
        }

        $rendiciones = $q->orderByDesc('id')->paginate(20)->withQueryString();

        return view('redeems.rendiciones-empresa.settlements-index', [
            'rendiciones' => $rendiciones,
            'estado'      => $estado,
        ]);
    }

    public function revertToPending(PointSettlement $settlement)
    {
        $u = Auth::user();

        if ($u->hasRole('negocio')) {
            abort_unless((int)$settlement->business_user_id === (int)$u->id, 403);
        }
        if ($u->hasRole('admin_empresa') && !$u->hasRole('admin_sitio')) {
            abort_unless(!empty($u->company_id) && (int)$settlement->company_id === (int)$u->company_id, 403);
        }

        if ($settlement->status === 'invoiced') {
            return back()->with('error', 'No se puede revertir una rendición ya facturada.');
        }

        DB::transaction(function () use ($settlement, $u) {
            PointRedemption::where('settlement_id', $settlement->id)->update([
                'settlement_id' => null,
                'updated_at'    => now(),
            ]);

            $settlement->status = 'cancelled';
            $settlement->note = trim(
                ($settlement->note ? $settlement->note . "\n" : '') .
                'Rendición anulada y consumos revertidos a pendiente por ' . ($u->name ?? 'usuario') .
                ' el ' . now()->format('d/m/Y H:i')
            );
            $settlement->save();
        });

        return redirect()
            ->route('redeems.rendiciones_empresa.index')
            ->with('success', 'Listo: la rendición se anuló y los consumos volvieron a Pendiente.');
    }
}
