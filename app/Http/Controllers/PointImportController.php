<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\PointMovement;
use App\Models\PointImportBatch;
use App\Models\PointReference;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PointsImportedMail;

class PointImportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin_sitio|admin_empresa']);
    }

    public function create(Request $r)
    {
        $u = $r->user();
        $isSiteAdmin = $u->hasRole('admin_sitio');

        $companies = $isSiteAdmin ? Company::orderBy('name')->get() : collect();

        // ESTA VISTA ES SOLO EL FORMULARIO
        return view('points.import.create', compact('isSiteAdmin','companies'));
    }

    public function preview(Request $r)
    {
        $u = $r->user();
        $isSiteAdmin = $u->hasRole('admin_sitio');

        $data = $r->validate([
            'company_id' => ['nullable','integer','exists:companies,id'],
            'file'       => ['required','file','mimes:csv,txt','max:5120'],
        ]);

        $companyId = $isSiteAdmin
            ? ((int)($data['company_id'] ?? 0) ?: (int)($u->company_id ?? 0))
            : (int)$u->company_id;

        if (!$companyId) return back()->with('error','No se pudo determinar la empresa.');

        $path = $r->file('file')->store('imports/points');
        $fullPath = Storage::path($path);

        $fh = fopen($fullPath, 'r');
        if (!$fh) return back()->with('error','No se pudo leer el archivo.');

        // Detectar delimitador por primera línea (coma o punto y coma)
        $firstLine = fgets($fh);
        if ($firstLine === false) { fclose($fh); return back()->with('error','CSV vacío o inválido.'); }

        $delim = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // Volver al inicio
        rewind($fh);

        // Leer header
        $header = fgetcsv($fh, 0, $delim);
        if (!$header) { fclose($fh); return back()->with('error','CSV vacío o inválido.'); }

        // Normalizar header + quitar BOM
        $header = array_map(function($h){
            $h = trim((string)$h);
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM UTF-8
            return strtolower($h);
        }, $header);

        $rows = [];
        $ok = 0;
        $err = 0;
        $line = 1;

        while (($cols = fgetcsv($fh, 0, $delim)) !== false) {
            $line++;

            if (count($cols) === 1 && trim((string)$cols[0]) === '') continue;

            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $cols[$i] ?? null;
            }

            // Alias de headers en español -> keys internas esperadas
            $aliases = [
                'cuil_empleado' => 'employee_cuil',
                'cuit_empleado' => 'employee_cuil',
                'puntos'        => 'points',
                'fecha'         => 'occurred_at',
                'referencia'    => 'reference',
                'nota'          => 'note',
            ];

            foreach ($aliases as $from => $to) {
                if (array_key_exists($from, $row) && (!array_key_exists($to, $row) || $row[$to] === null || $row[$to] === '')) {
                    $row[$to] = $row[$from];
                }
            }

            $validation = $this->validateImportRow($row, $companyId, $isSiteAdmin, $u);

            $rows[] = [
                'line'   => $line,
                'row'    => $row,
                'ok'     => $validation['ok'],
                'error'  => $validation['error'],
                'mapped' => $validation['mapped'],
            ];

            $validation['ok'] ? $ok++ : $err++;

            if (count($rows) >= 5000) break;
        }

        fclose($fh);

        // Guardar para commit
        session([
            'points_import.path'      => $path,
            'points_import.companyId' => $companyId,
            'points_import.rows'      => $rows,
            'points_import.ok'        => $ok,
            'points_import.err'       => $err,
        ]);

        // ====== Adaptar al contrato que tu preview.blade.php espera ======
        $batch = (object)[
            'id'         => null,      // todavía no existe en BD (se crea en commit)
            'filename'   => $path,
            'rows_total' => count($rows),
            'rows_ok'    => $ok,
            'rows_error' => $err,
        ];

        $preview = array_map(function($r){
            return [
                'line'          => $r['line'],
                'employee_cuil' => $r['row']['employee_cuil'] ?? null,
                'points'        => $r['row']['points'] ?? null,
                'occurred_at'   => $r['row']['occurred_at'] ?? null,
                'reference'     => $r['row']['reference'] ?? null,
                'status'        => $r['ok'] ? 'ok' : 'error',
                'issues'        => $r['ok'] ? [] : [($r['error'] ?? 'Error desconocido')],
            ];
        }, $rows);

        return view('points.import.preview', compact('companyId','path','batch','preview'));
    }

    public function commit(Request $r)
    {
        $u = $r->user();

    $path      = session('points_import.path');
    $companyId = (int)session('points_import.companyId');
    $rows      = session('points_import.rows', []);
    $okCount   = (int)session('points_import.ok', 0);
    $errCount  = (int)session('points_import.err', 0);

    if (!$path || !$companyId || empty($rows)) {
        return redirect()->route('points.import.create')->with('error','No hay preview para confirmar.');
    }

    $r->validate(['confirm' => ['required','in:1']]);


        // Acumulador: [employee_user_id => ['points' => int, 'reference' => ?string]]
    $notify = [];

    $batch = null;

        DB::transaction(function () use ($u, $companyId, $path, $rows, $okCount, $errCount, &$notify, &$batch) {

        $batch = PointImportBatch::create([
            'company_id'  => $companyId,
            'created_by'  => $u->id,
            'filename'    => $path,
            'status'      => 'committed',
            'rows_total'  => count($rows),
            'rows_ok'     => $okCount,
            'rows_error'  => $errCount,
        ]);

        foreach ($rows as $rrow) {
            if (!$rrow['ok']) continue;

            $m = $rrow['mapped'];

            PointMovement::create([
                'company_id'        => $companyId,
                'employee_user_id'  => $m['employee_user_id'],
                'business_user_id'  => null,
                'created_by'        => $u->id,
                'confirmed_by'      => null,
                'batch_id'          => $batch->id,
                'type'              => $m['type'],
                'points'            => $m['points'],
                'money_amount'      => null,
                'reference'         => $m['reference'] ?? null,
                'note'              => $m['note'] ?? null,
                'occurred_at'       => $m['occurred_at'] ?? now(),
            ]);

            // Acumular para notificar (1 mail por usuario)
            $empId = (int)$m['employee_user_id'];
            if (!isset($notify[$empId])) {
                $notify[$empId] = [
                    'points'    => 0,
                    'reference' => $m['reference'] ?? null,
                ];
            }
            $notify[$empId]['points'] += (int)$m['points'];

            // Si querés, podrías priorizar una reference no vacía:
            if (empty($notify[$empId]['reference']) && !empty($m['reference'])) {
                $notify[$empId]['reference'] = $m['reference'];
            }
        }
    });

    // Enviar correos (fuera de la transacción)
    if (!empty($notify) && $batch) {
        $users = User::query()
            ->whereIn('id', array_keys($notify))
            ->get(['id','name','email']);

        foreach ($users as $user) {
            $delta = (int)($notify[$user->id]['points'] ?? 0);
            if ($delta === 0) continue;

            if (!empty($user->email)) {
                Mail::to($user->email)->send(
                    new \App\Mail\PointsImportedMail(
                        $user,
                        $batch,
                        $delta,
                        $notify[$user->id]['reference'] ?? null
                    )
                );
            }
        }
    }

    session()->forget([
        'points_import.path','points_import.companyId','points_import.rows','points_import.ok','points_import.err'
    ]);

    return redirect()->route('points.index')->with('ok','Importación confirmada y aplicada.');
}

    private function validateImportRow(array $row, int $companyId, bool $isSiteAdmin, $u): array
    {
        $employeeId   = $row['employee_id'] ?? null;
        $employeeCuil = $row['employee_cuil'] ?? ($row['employee_cuit'] ?? null);
        $employeeMail = $row['employee_email'] ?? ($row['email'] ?? null);

        // Default type = adjust (para plantilla en español sin "type")
        $type = strtolower(trim((string)($row['type'] ?? 'adjust')));
        $points = (int)($row['points'] ?? 0);

        if (!$employeeId && !$employeeCuil && !$employeeMail) {
            return ['ok'=>false,'error'=>'Falta employee_id / employee_cuil / employee_email','mapped'=>null];
        }

        if (!in_array($type, ['earn','redeem','adjust','expire'], true)) {
            return ['ok'=>false,'error'=>'Type inválido (earn/redeem/adjust/expire)','mapped'=>null];
        }

        if ($points <= 0) {
            return ['ok'=>false,'error'=>'Points debe ser > 0','mapped'=>null];
        }

        $empQ = User::query()->whereHas('roles', fn($q) => $q->where('name','empleado'));

        if ($employeeId) {
            $empQ->whereKey((int)$employeeId);
        } elseif ($employeeMail) {
            $empQ->where('email', trim((string)$employeeMail));
        } else {
            $empQ->where('cuil', preg_replace('/\D+/', '', (string)$employeeCuil));
        }

        $emp = $empQ->first();
        if (!$emp) return ['ok'=>false,'error'=>'Empleado no encontrado','mapped'=>null];

        if (!$isSiteAdmin) {
            if ((int)$emp->company_id !== (int)($u->company_id ?? 0)) {
                return ['ok'=>false,'error'=>'Empleado fuera de tu empresa','mapped'=>null];
            }
        } else {
            if ((int)$emp->company_id !== (int)$companyId) {
                return ['ok'=>false,'error'=>'Empleado no pertenece a la empresa seleccionada','mapped'=>null];
            }
        }

        // signed points
        $signedPoints = in_array($type, ['redeem','expire'], true) ? -abs($points) : abs($points);

        // occurred_at opcional (soporta dd/mm/aaaa o ISO)
        $occurredAt = null;
        if (!empty($row['occurred_at'])) {
            $rawDate = trim((string)$row['occurred_at']);

            try {
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $rawDate)) {
                    $occurredAt = Carbon::createFromFormat('d/m/Y', $rawDate);
                } else {
                    $occurredAt = Carbon::parse($rawDate);
                }
            } catch (\Throwable $e) {
                return ['ok'=>false,'error'=>'Fecha inválida (usar dd/mm/aaaa o aaaa-mm-dd)','mapped'=>null];
            }
        }

        // reference texto (si viene, intentamos mapear por name)
        $refText = null;
        if (!empty($row['reference'])) {
            $candidate = trim((string)$row['reference']);

            $mapped = PointReference::query()
                ->active()
                ->forCompany((int)$emp->company_id)
                ->where('name', $candidate)
                ->value('name');

            $refText = $mapped ?: $candidate;
        }

        return [
            'ok' => true,
            'error' => null,
            'mapped' => [
                'employee_user_id' => $emp->id,
                'type'             => $type,
                'points'           => $signedPoints,
                'reference'        => $refText,
                'note'             => $row['note'] ?? null,
                'occurred_at'      => $occurredAt,
            ],
        ];
    }
}
