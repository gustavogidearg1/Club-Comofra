<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\PointMovement;
use App\Models\PointReference;
use App\Models\PointImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PointsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * LISTADO / INDEX
     */
    public function index(Request $r)
    {
        $u = $r->user();

        $isSiteAdmin    = $u->hasRole('admin_sitio');
        $isCompanyAdmin = $u->hasRole('admin_empresa');
        $isEmployee     = $u->hasRole('empleado');

        if (!$isSiteAdmin && !$isCompanyAdmin && !$isEmployee) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para ver puntos.');
        }

        $hasVoided = Schema::hasColumn('point_movements', 'voided_at');

        // ===== EMPLEADO (vista propia) =====
        if ($isEmployee && !$isSiteAdmin && !$isCompanyAdmin) {

            $q = PointMovement::query()
                ->where('employee_user_id', $u->id)
                ->with(['business:id,name', 'company:id,name'])
                ->orderByDesc('occurred_at')
                ->orderByDesc('id');

            if ($hasVoided) $q->whereNull('voided_at');

            $per = (int)($r->get('per', 25));
            if (!in_array($per, [15, 25, 50, 100], true)) $per = 25;

            $points = $q->paginate($per)->withQueryString();

            $totals = $this->computeTotalsForEmployee($u->id, $hasVoided);

            return view('points.empleado-view', compact('points', 'totals'));
        }

        // ===== ADMINS =====
        $q = PointMovement::query()
            ->with([
                'employee:id,name,email,company_id,cuil',
                'business:id,name',
                'company:id,name',
                'createdBy:id,name',
                'confirmedBy:id,name',
                'batch:id,filename,rows_total',
            ]);

        // Restricción por empresa para admin_empresa
        if ($isCompanyAdmin && !$isSiteAdmin) {
            $q->where('company_id', $u->company_id);
        }

        $term = trim((string)$r->get('q'));
        if ($term !== '') {
            $q->where(function ($qq) use ($term) {
                $qq->whereHas('employee', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('business', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                    ->orWhere('note', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%");
            });
        }

        if ($r->filled('company_id') && $isSiteAdmin) {
            $q->where('company_id', (int)$r->company_id);
        }

        if ($r->filled('employee_id')) $q->where('employee_user_id', (int)$r->employee_id);
        if ($r->filled('type') && $r->type !== 'all') $q->where('type', $r->type);
        if ($r->filled('batch_id')) $q->where('batch_id', (int)$r->batch_id);

        if ($r->filled('start_date')) $q->whereDate('occurred_at', '>=', $r->start_date);
        if ($r->filled('end_date'))   $q->whereDate('occurred_at', '<=', $r->end_date);

        if ($hasVoided && !$r->boolean('show_voided')) {
            $q->whereNull('voided_at');
        }

        $per = (int)($r->get('per', 15));
        if (!in_array($per, [15, 25, 50, 100], true)) $per = 15;

        $q->orderByDesc('occurred_at')->orderByDesc('id');

        $points = $q->paginate($per)->withQueryString();

        $employees = User::query()
            ->whereHas('roles', fn($qq) => $qq->where('name', 'empleado'))
            ->when(!$isSiteAdmin, fn($qq) => $qq->where('company_id', $u->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = $isSiteAdmin
            ? Company::orderBy('name')->get(['id', 'name'])
            : collect();

        $types = ['earn', 'redeem', 'adjust', 'expire'];

        $batches = PointImportBatch::query()
            ->when(!$isSiteAdmin, fn($qq) => $qq->where('company_id', $u->company_id))
            ->orderByDesc('id')
            ->limit(200)
            ->get(['id', 'filename', 'rows_total']);

        $stats = $this->computeStatsForQuery(clone $q);

        return view('points.index', [
            'points'         => $points,
            'q'              => $term,
            'employees'      => $employees,
            'companies'      => $companies,
            'types'          => $types,
            'batches'        => $batches,
            'stats'          => $stats,
            'isSiteAdmin'    => $isSiteAdmin,
            'isCompanyAdmin' => $isCompanyAdmin,
        ]);
    }

    /**
     * RESUMEN (admins)
     */
    public function summary(Request $r)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId   = $isSiteAdmin ? ($r->company_id ?: null) : ($u->company_id ?? null);

        $hasVoided = Schema::hasColumn('point_movements', 'voided_at');

        $employeesQ = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'empleado'))
            ->with('company:id,name');

        if (!$isSiteAdmin && $companyId) $employeesQ->where('company_id', $companyId);
        elseif ($isSiteAdmin && !empty($companyId)) $employeesQ->where('company_id', $companyId);

        $employeeQ = trim((string)$r->get('employee_q'));
        if ($employeeQ !== '') {
            $employeesQ->where(function ($qq) use ($employeeQ) {
                $qq->where('users.name', 'like', "%{$employeeQ}%")
                    ->orWhere('users.email', 'like', "%{$employeeQ}%");
            });
        }

        $cuil = preg_replace('/\D+/', '', (string)$r->get('cuil'));
        if (!empty($cuil)) {
            $employeesQ->where(function ($qq) use ($cuil) {
                $qq->whereRaw("REPLACE(REPLACE(users.cuil,'-',''),' ','') LIKE ?", ["%{$cuil}%"]);
            });
        }

        // Totales por empleado (redeem negativo => abs)
        $movQ = PointMovement::query()
            ->selectRaw('employee_user_id,
                SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as total_earned,
                ABS(SUM(CASE WHEN points < 0 THEN points ELSE 0 END)) as total_redeemed,
                SUM(points) as total_available,
                COUNT(*) as movement_count
            ')
            ->groupBy('employee_user_id');

        if ($hasVoided) $movQ->whereNull('voided_at');
        if (!empty($companyId)) $movQ->where('company_id', $companyId);

        $summaryQ = $employeesQ
            ->leftJoinSub($movQ, 'm', fn($join) => $join->on('users.id', '=', 'm.employee_user_id'))
            ->addSelect([
                'users.id', 'users.name', 'users.email', 'users.cuil', 'users.company_id',
                DB::raw('COALESCE(m.total_earned,0) as total_earned'),
                DB::raw('COALESCE(m.total_redeemed,0) as total_redeemed'),
                DB::raw('COALESCE(m.total_available,0) as total_available'),
                DB::raw('COALESCE(m.movement_count,0) as movement_count'),
            ])
            ->orderByDesc('total_available')
            ->orderBy('users.name');

        $summary = $summaryQ->paginate(25)->withQueryString();

        $overallMov = PointMovement::query();
        if ($hasVoided) $overallMov->whereNull('voided_at');
        if (!empty($companyId)) $overallMov->where('company_id', $companyId);

        $earned    = (int)(clone $overallMov)->where('type', 'earn')->sum('points');
        $redeemAbs = (int)abs((clone $overallMov)->where('type', 'redeem')->sum('points'));

        $overallTotals = [
            'total_employees'        => (clone $employeesQ)->count(),
            'total_points_earned'    => $earned,
            'total_points_redeemed'  => $redeemAbs,
            'total_points_available' => $earned - $redeemAbs,
        ];

        $companies = $isSiteAdmin ? Company::orderBy('name')->get(['id', 'name']) : collect();

        return view('points.resumen', compact('summary', 'overallTotals', 'companies', 'isSiteAdmin', 'companyId'));
    }

    /**
     * CREATE (admins)
     */
    public function create(Request $r)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId   = $isSiteAdmin ? null : ($u->company_id ?? null);

        $employeesQ = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'empleado'))
            ->with('company:id,name');

        if (!$isSiteAdmin && $companyId) $employeesQ->where('company_id', $companyId);

        $employees = $employeesQ->orderBy('name')->get();

        $companies = $isSiteAdmin ? Company::orderBy('name')->get() : collect();

        // Referencias activas (globales + empresa)
        $references = PointReference::query()
            ->active()
            ->forCompany($companyId)
            ->orderByRaw('COALESCE(sort_order, 9999) ASC')
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        $types = [
            'earn'   => 'Acreditación',
            'redeem' => 'Canje / Consumo',
            'adjust' => 'Ajuste',
            'expire' => 'Vencimiento',
        ];

        return view('points.crear', compact('employees', 'companies', 'companyId', 'isSiteAdmin', 'types', 'references'));
    }

    /**
     * STORE (admins) + mail al empleado
     */
    public function store(Request $r)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId   = $isSiteAdmin ? null : ($u->company_id ?? null);

        $data = $r->validate([
            'company_id'        => ['nullable', 'integer', 'exists:companies,id'],
            'employee_user_id'  => ['required', 'integer', 'exists:users,id'],
            'type'              => ['required', 'in:earn,redeem,adjust,expire'],
            'points'            => ['required', 'integer', 'min:1', 'max:1000000'],
            'occurred_at'       => ['nullable', 'date'],
            'reference_id'      => ['required', 'integer', 'exists:point_references,id'],
            'note'              => ['nullable', 'string', 'max:500'],
        ]);

        $employee = User::query()
            ->whereKey($data['employee_user_id'])
            ->whereHas('roles', fn($q) => $q->where('name', 'empleado'))
            ->firstOrFail();

        if (!$isSiteAdmin) {
            abort_unless((int)$employee->company_id === (int)$companyId, 403);
        }

        $finalCompanyId = $isSiteAdmin
            ? ((int)($data['company_id'] ?? 0) ?: (int)$employee->company_id)
            : (int)$employee->company_id;

        $occurredAt = !empty($data['occurred_at'])
            ? Carbon::parse($data['occurred_at'])
            : now();

        $refText = PointReference::query()
            ->whereKey((int)$data['reference_id'])
            ->value('name');

        if (empty($refText)) {
            return back()->withErrors(['reference_id' => 'Referencia inválida.'])->withInput();
        }

        $pts = (int)$data['points'];
        if (in_array($data['type'], ['redeem', 'expire'], true)) $pts = -abs($pts);
        else $pts = abs($pts);

        $movement = null;

        DB::transaction(function () use ($u, $employee, $finalCompanyId, $occurredAt, $data, $refText, $pts, &$movement) {
            $movement = PointMovement::create([
                'company_id'       => $finalCompanyId,
                'employee_user_id' => $employee->id,
                'business_user_id' => null,

                'created_by'       => $u->id,
                'confirmed_by'     => null,
                'batch_id'         => null,

                'type'             => $data['type'],
                'points'           => $pts,
                'money_amount'     => null,

                'reference'        => $refText,
                'note'             => $data['note'] ?? null,
                'occurred_at'      => $occurredAt,
            ]);
        });

        // Mail al empleado (fuera de transacción)
        try {
            $employee->refresh();
            if (!empty($employee->email)) {
                $employee->notify(new \App\Notifications\MovimientoPuntosCreado($movement));
            }
        } catch (\Throwable $e) {
            // opcional: loggear si querés
        }

        return redirect()->route('points.index')->with('ok', 'Movimiento guardado.');
    }

    /**
     * EDIT (admins)
     */
    public function edit(Request $r, PointMovement $movement)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId   = $isSiteAdmin ? null : ($u->company_id ?? null);

        $movement->load([
            'employee:id,name,email,company_id,cuil',
            'company:id,name',
            'createdBy:id,name',
        ]);

        if (!$isSiteAdmin) {
            abort_unless((int)$movement->company_id === (int)$companyId, 403);
        }

        // referencias disponibles según empresa del movimiento
        $references = PointReference::query()
            ->active()
            ->forCompany((int)$movement->company_id)
            ->orderByRaw('COALESCE(sort_order, 9999) ASC')
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        // Mapear texto guardado -> id referencia (si coincide por name)
        $currentRefId = null;
        if (!empty($movement->reference)) {
            $currentRefId = PointReference::query()
                ->active()
                ->forCompany((int)$movement->company_id)
                ->where('name', $movement->reference)
                ->value('id');
        }

        $types = [
            'earn'   => 'Acreditación',
            'redeem' => 'Canje / Consumo',
            'adjust' => 'Ajuste',
            'expire' => 'Vencimiento',
        ];

        return view('points.edit', compact('movement', 'types', 'references', 'currentRefId', 'isSiteAdmin'));
    }

    /**
     * UPDATE (admins) + mail indicando "editado/corregido"
     */
    public function update(Request $r, PointMovement $movement)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $companyId   = $isSiteAdmin ? null : ($u->company_id ?? null);

        $movement->load(['employee:id,name,email,company_id', 'company:id,name']);

        if (!$isSiteAdmin) {
            abort_unless((int)$movement->company_id === (int)$companyId, 403);
        }

        $data = $r->validate([
            'type'         => ['required', 'in:earn,redeem,adjust,expire'],
            'points'       => ['required', 'integer', 'min:1', 'max:1000000'],
            'occurred_at'  => ['nullable', 'date'],
            'reference_id' => ['required', 'integer', 'exists:point_references,id'],
            'note'         => ['nullable', 'string', 'max:500'],
        ]);

        // BEFORE
        $before = [
            'Tipo'      => (string)($movement->type ?? ''),
            'Puntos'    => (string)($movement->points ?? ''),
            'Fecha'     => optional($movement->occurred_at)->format('d/m/Y H:i') ?? '',
            'Referencia'=> (string)($movement->reference ?? ''),
            'Detalle'   => (string)($movement->note ?? ''),
        ];

        $occurredAt = !empty($data['occurred_at'])
            ? Carbon::parse($data['occurred_at'])
            : $movement->occurred_at;

        $refText = PointReference::query()
            ->whereKey((int)$data['reference_id'])
            ->value('name');

        if (empty($refText)) {
            return back()->withErrors(['reference_id' => 'Referencia inválida.'])->withInput();
        }

        // puntos firmados
        $pts = (int)$data['points'];
        if (in_array($data['type'], ['redeem', 'expire'], true)) $pts = -abs($pts);
        else $pts = abs($pts);

        DB::transaction(function () use ($movement, $data, $pts, $occurredAt, $refText, $u) {
            $movement->type        = $data['type'];
            $movement->points      = $pts;
            $movement->occurred_at = $occurredAt;
            $movement->reference   = $refText;
            $movement->note        = $data['note'] ?? null;

            // opcional: marcar quién corrigió (si tenés columnas)
            if (Schema::hasColumn('point_movements', 'updated_by')) {
                $movement->updated_by = $u->id;
            }

            $movement->save();
        });

        $movement->refresh();

        // AFTER
        $after = [
            'Tipo'      => (string)($movement->type ?? ''),
            'Puntos'    => (string)($movement->points ?? ''),
            'Fecha'     => optional($movement->occurred_at)->format('d/m/Y H:i') ?? '',
            'Referencia'=> (string)($movement->reference ?? ''),
            'Detalle'   => (string)($movement->note ?? ''),
        ];

        // Cambios (solo lo distinto)
        $changes = [];
        foreach ($before as $label => $valBefore) {
            $valAfter = $after[$label] ?? '';
            if ((string)$valBefore !== (string)$valAfter) {
                $changes[$label] = ['before' => ($valBefore === '' ? '—' : $valBefore), 'after' => ($valAfter === '' ? '—' : $valAfter)];
            }
        }

        // Mail al empleado indicando corrección
        try {
            $employee = $movement->employee;
            if ($employee && !empty($employee->email)) {
                $employee->notify(new \App\Notifications\MovimientoPuntosEditado($movement, $changes));
            }
        } catch (\Throwable $e) {
            // opcional: log
        }

        return redirect()->route('points.index')->with('ok', 'Movimiento actualizado.');
    }

    /**
     * Detalle por empleado (admins)
     */
    public function employeeDetail(Request $r, User $employee)
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $hasVoided   = Schema::hasColumn('point_movements', 'voided_at');

        abort_unless($employee->hasRole('empleado'), 404);

        if (!$isSiteAdmin) {
            abort_unless((int)$employee->company_id === (int)($u->company_id ?? 0), 403);
        }

        $q = PointMovement::query()
            ->where('employee_user_id', $employee->id)
            ->with(['business:id,name', 'createdBy:id,name', 'company:id,name']);

        if ($hasVoided) $q->whereNull('voided_at');

        if ($r->filled('type'))       $q->where('type', $r->type);
        if ($r->filled('start_date')) $q->whereDate('occurred_at', '>=', $r->start_date);
        if ($r->filled('end_date'))   $q->whereDate('occurred_at', '<=', $r->end_date);

        $q->orderByDesc('occurred_at')->orderByDesc('id');

        $points = $q->paginate(25)->withQueryString();
        $totals = $this->computeTotalsForEmployee($employee->id, $hasVoided);

        return view('points.empleado-detalle', [
            'employee' => $employee->load('company:id,name'),
            'points'   => $points,
            'totals'   => $totals,
        ]);
    }

    /**
     * EXPORT CSV (admins)
     */
    public function export(Request $r): StreamedResponse
    {
        $u = $r->user();
        abort_unless($u->hasRole('admin_sitio') || $u->hasRole('admin_empresa'), 403);

        $isSiteAdmin = $u->hasRole('admin_sitio');
        $hasVoided   = Schema::hasColumn('point_movements', 'voided_at');

        $q = PointMovement::query()
            ->with([
                'employee:id,name,email,company_id,cuil',
                'business:id,name',
                'company:id,name',
                'createdBy:id,name',
                'confirmedBy:id,name',
                'batch:id,filename,rows_total',
            ]);

        if (!$isSiteAdmin) {
            $q->where('company_id', $u->company_id);
        }

        $term = trim((string)$r->get('q'));
        if ($term !== '') {
            $q->where(function ($qq) use ($term) {
                $qq->whereHas('employee', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('business', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                    ->orWhere('note', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%");
            });
        }

        if ($isSiteAdmin && $r->filled('company_id')) $q->where('company_id', (int)$r->company_id);
        if ($r->filled('employee_id')) $q->where('employee_user_id', (int)$r->employee_id);
        if ($r->filled('type') && $r->type !== 'all') $q->where('type', $r->type);
        if ($r->filled('batch_id')) $q->where('batch_id', (int)$r->batch_id);

        if ($r->filled('start_date')) $q->whereDate('occurred_at', '>=', $r->start_date);
        if ($r->filled('end_date'))   $q->whereDate('occurred_at', '<=', $r->end_date);

        if ($hasVoided && !$r->boolean('show_voided')) {
            $q->whereNull('voided_at');
        }

        $q->orderByDesc('occurred_at')->orderByDesc('id');

        $filename = 'points_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');

            // BOM para Excel (tildes, ñ, etc.)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Fecha', 'Empresa', 'Empleado', 'CUIL', 'Negocio', 'Tipo', 'Puntos', 'Referencia', 'Nota', 'Creado por', 'Lote'
            ]);

            $q->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    fputcsv($out, [
                        $m->id,
                        optional($m->occurred_at)->format('Y-m-d H:i:s'),
                        $m->company?->name,
                        $m->employee?->name,
                        $m->employee?->cuil,
                        $m->business?->name,
                        $m->type,
                        $m->points,
                        $m->reference,
                        $m->note,
                        $m->createdBy?->name,
                        $m->batch ? basename($m->batch->filename) : null,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * ANULAR MOVIMIENTO (si existe voided_at)
     */
    public function void(Request $r, PointMovement $movement)
    {
        $u = $r->user();

        $isSiteAdmin    = $u->hasRole('admin_sitio');
        $isCompanyAdmin = $u->hasRole('admin_empresa');

        abort_unless($isSiteAdmin || $isCompanyAdmin, 403);

        if (!Schema::hasColumn('point_movements', 'voided_at')) {
            return back()->with('error', 'Tu tabla no tiene voided_at.');
        }

        if (!$isSiteAdmin) {
            abort_unless((int)$movement->company_id === (int)($u->company_id ?? 0), 403);
        }

        if ($movement->voided_at) return back()->with('error', 'El movimiento ya estaba anulado.');

        $movement->voided_at = now();
        if (Schema::hasColumn('point_movements', 'voided_by')) $movement->voided_by = $u->id;

        $movement->save();

        return back()->with('ok', 'Movimiento anulado.');
    }

    /**
     * Helpers
     */
    private function computeTotalsForEmployee(int $employeeUserId, bool $hasVoided): array
    {
        $q = PointMovement::query()->where('employee_user_id', $employeeUserId);
        if ($hasVoided) $q->whereNull('voided_at');

        $earned    = (int)(clone $q)->where('points', '>', 0)->sum('points');
        $redeemAbs = (int)abs((clone $q)->where('points', '<', 0)->sum('points'));
        $available = (int)(clone $q)->sum('points');

        return [
            'total_earned'   => $earned,
            'total_redeemed' => $redeemAbs,
            'available'      => $available,
        ];
    }

    private function computeStatsForQuery($q): array
    {
        $totalEarned    = (int)(clone $q)->where('points', '>', 0)->sum('points');
        $totalRedeemAbs = (int)abs((clone $q)->where('points', '<', 0)->sum('points'));

        $totalMovements = (int)(clone $q)->count();
        $totalPoints    = (int)(clone $q)->sum('points');

        $avg = $totalMovements > 0 ? ($totalPoints / $totalMovements) : 0;

        return [
            'total_points'    => $totalPoints,
            'total_earned'    => $totalEarned,
            'total_redeemed'  => $totalRedeemAbs,
            'total_movements' => $totalMovements,
            'avg_points'      => $avg,
        ];
    }
}
