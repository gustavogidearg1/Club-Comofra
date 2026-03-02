<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use App\Models\User;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PointImportController;
use App\Http\Controllers\RedemptionController;

// ABM
use App\Http\Controllers\Abm\CompanyController;
use App\Http\Controllers\Abm\PaisController;
use App\Http\Controllers\Abm\ProvinciaController;
use App\Http\Controllers\Abm\LocalidadController;
use App\Http\Controllers\Abm\UserController;
use App\Http\Controllers\Abm\PointReferenceController;

// Rendiciones
use App\Http\Controllers\SettlementController;

Route::get('/', fn() => redirect()->route('login'));

/**
 * Dashboard
 */
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/**
 * Perfil
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * ============================
 * TODO LO QUE REQUIERE LOGIN
 * ============================
 */
Route::middleware(['auth'])->group(function () {

    /**
     * Debug de rutas (solo dev)
     */
    Route::get('/debug/route/{any}', function ($any) {
        $path = ltrim($any, '/');
        $request = request()->create('/' . $path, 'GET');
        $route = app('router')->getRoutes()->match($request);

        dd([
            'target' => '/' . $path,
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'middleware' => $route->gatherMiddleware(),
            'roles' => auth()->user()->getRoleNames(),
            'has_admin_empresa' => auth()->user()->hasRole('admin_empresa'),
            'has_admin_sitio' => auth()->user()->hasRole('admin_sitio'),
        ]);
    })->where('any', '.*');

    /**
     * ============================
     * REDENCIONES (QR) - Negocio / Empleado
     * ============================
     */
    Route::get('/redeems/create', [RedemptionController::class, 'create'])
        ->name('redeems.create');

    Route::post('/redeems', [RedemptionController::class, 'store'])
        ->name('redeems.store');

    Route::patch('/redeems/{redemption}/cancel', [RedemptionController::class, 'cancel'])
        ->name('redeems.cancel');

    Route::get('/redeems/confirm/{token}', [RedemptionController::class, 'showConfirm'])
        ->name('redeems.confirm.show');

    Route::post('/redeems/confirm/{token}', [RedemptionController::class, 'confirm'])
        ->name('redeems.confirm.do');

    /**
     * ============================
     * RENDICIONES A EMPRESA (admin/negocio)
     * ============================
     */
    Route::middleware(['role:admin_sitio|admin_empresa|negocio'])
        ->prefix('redeems/rendiciones-empresa')
        ->name('redeems.rendiciones_empresa.')
        ->group(function () {

            // Index consumos a rendir
            Route::get('/', [SettlementController::class, 'consumosIndex'])
                ->name('index');

            // Crear rendición con consumos seleccionados
            Route::post('/', [SettlementController::class, 'store'])
                ->name('store');

            // Listado de rendiciones realizadas
            Route::get('/rendiciones', [SettlementController::class, 'settlementsIndex'])
                ->name('settlements');

            // Ver rendición
            Route::get('/{settlement}', [SettlementController::class, 'show'])
                ->name('show');

            // Marcar como facturada
            Route::post('/{settlement}/facturar', [SettlementController::class, 'markInvoiced'])
                ->name('facturar');

            // Revertir a pendiente (anula rendición y devuelve consumos)
            Route::post('/{settlement}/revertir', [SettlementController::class, 'revertToPending'])
                ->name('revertir');
        });

    /**
     * Endpoint JSON para completar negocio desde QR
     * Ej: /abm/businesses/1/json
     */
    Route::get('/abm/businesses/{id}/json', function ($id) {
        $u = User::query()
            ->whereKey($id)
            ->whereHas('roles', fn($q) => $q->where('name', 'negocio'))
            ->firstOrFail();

        return response()->json([
            'id'   => $u->id,
            'name' => $u->name,
        ]);
    })->name('abm.businesses.json');

    /**
     * ============================
     * CONSUMO MANUAL (Empleado -> Negocio)
     * ============================
     */
    Route::get('/redeems/manual', [RedemptionController::class, 'manualIndex'])
        ->name('redeems.manual.index');

    Route::get('/redeems/manual/{business}', [RedemptionController::class, 'manualCreate'])
        ->name('redeems.manual.create');

    Route::post('/redeems/manual/{business}', [RedemptionController::class, 'manualStore'])
        ->name('redeems.manual.store');

    /**
     * ============================
     * PUNTOS
     * ============================
     */
    Route::prefix('points')->name('points.')->group(function () {

        Route::get('/', [PointsController::class, 'index'])->name('index');

        Route::get('/summary', [PointsController::class, 'summary'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('summary');

        Route::get('/resumen', [PointsController::class, 'summary'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('resumen');

        Route::get('/crear', [PointsController::class, 'create'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('create');

        Route::post('/guardar', [PointsController::class, 'store'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('store');

        Route::get('/{movement}/edit', [PointsController::class, 'edit'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('edit');

        Route::put('/{movement}', [PointsController::class, 'update'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('update');

        Route::patch('/{movement}/void', [PointsController::class, 'void'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('void');

        Route::get('/employee/{employee}', [PointsController::class, 'employeeDetail'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('employee.detail');

        Route::get('/export', [PointsController::class, 'export'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('export');
    });

    /**
     * ============================
     * IMPORTACIÓN MASIVA (admins)
     * ============================
     */
    Route::middleware(['role:admin_sitio|admin_empresa'])->group(function () {
        Route::get('/points/import', [PointImportController::class, 'create'])
            ->name('points.import.create');

        Route::post('/points/import/preview', [PointImportController::class, 'preview'])
            ->name('points.import.preview');

        Route::post('/points/import/commit', [PointImportController::class, 'commit'])
            ->name('points.import.commit');
    });

    /**
     * ============================
     * ABM
     * ============================
     */
    Route::middleware(['role:admin_sitio|admin_empresa'])
        ->prefix('abm')
        ->name('abm.')
        ->group(function () {

            Route::resource('paises', PaisController::class)
                ->parameters(['paises' => 'pais'])
                ->names('paises');

            Route::resource('provincias', ProvinciaController::class)
                ->parameters(['provincias' => 'provincia'])
                ->names('provincias');

            Route::resource('localidades', LocalidadController::class)
                ->parameters(['localidades' => 'localidad'])
                ->names('localidades');

            // Users (ABM a mano)
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

            Route::resource('point-references', PointReferenceController::class)
                ->names('point-references');

            // SOLO admin_sitio puede administrar empresas
            Route::middleware(['role:admin_sitio'])->group(function () {
                Route::resource('companies', CompanyController::class);
            });
        });
});

require __DIR__ . '/auth.php';
