<?php

use Illuminate\Support\Facades\Route;

use App\Models\User;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PointImportController;
use App\Http\Controllers\RedemptionController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\DashboardController;

// ABM
use App\Http\Controllers\Abm\CompanyController;
use App\Http\Controllers\Abm\PaisController;
use App\Http\Controllers\Abm\ProvinciaController;
use App\Http\Controllers\Abm\LocalidadController;
use App\Http\Controllers\Abm\UserController;
use App\Http\Controllers\Abm\PointReferenceController;

Route::get('/', fn() => redirect()->route('login'));

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
 * Todo lo que requiere login
 */
Route::middleware(['auth'])->group(function () {

    /**
     * Dashboard
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * Debug de rutas
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
     * Redenciones
     */
    Route::get('/redeems/create', [RedemptionController::class, 'create'])->name('redeems.create');
    Route::post('/redeems', [RedemptionController::class, 'store'])->name('redeems.store');
    Route::patch('/redeems/{redemption}/cancel', [RedemptionController::class, 'cancel'])->name('redeems.cancel');

    Route::get('/redeems/confirm/{token}', [RedemptionController::class, 'showConfirm'])->name('redeems.confirm.show');
    Route::post('/redeems/confirm/{token}', [RedemptionController::class, 'confirm'])->name('redeems.confirm.do');

    /**
     * Rendiciones a empresa
     */
    Route::middleware(['role:admin_sitio|admin_empresa|negocio'])
        ->prefix('redeems/rendiciones-empresa')
        ->name('redeems.rendiciones_empresa.')
        ->group(function () {
            Route::get('/', [SettlementController::class, 'consumosIndex'])->name('index');
            Route::post('/', [SettlementController::class, 'store'])->name('store');
            Route::get('/rendiciones', [SettlementController::class, 'settlementsIndex'])->name('settlements');
            Route::get('/{settlement}', [SettlementController::class, 'show'])->name('show');
            Route::post('/{settlement}/facturar', [SettlementController::class, 'markInvoiced'])->name('facturar');
            Route::post('/{settlement}/revertir', [SettlementController::class, 'revertToPending'])->name('revertir');
        });

    /**
     * Endpoint JSON negocio
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
     * Consumo manual
     */
    Route::get('/redeems/manual', [RedemptionController::class, 'manualIndex'])->name('redeems.manual.index');
    Route::get('/redeems/manual/{business}', [RedemptionController::class, 'manualCreate'])->name('redeems.manual.create');
    Route::post('/redeems/manual/{business}', [RedemptionController::class, 'manualStore'])->name('redeems.manual.store');

    /**
     * Puntos
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

        Route::get('/export', [PointsController::class, 'export'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('export');

        Route::get('/employee/{employee}', [PointsController::class, 'employeeDetail'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('employee.detail');

        Route::get('/{movement}/edit', [PointsController::class, 'edit'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('edit');

        Route::put('/{movement}', [PointsController::class, 'update'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('update');

        Route::patch('/{movement}/void', [PointsController::class, 'void'])
            ->middleware('role:admin_sitio|admin_empresa')
            ->name('void');
    });

    /**
     * Importación masiva de puntos
     */
    Route::middleware(['role:admin_sitio|admin_empresa'])->group(function () {
        Route::get('/points/import', [PointImportController::class, 'create'])->name('points.import.create');
        Route::post('/points/import/preview', [PointImportController::class, 'preview'])->name('points.import.preview');
        Route::post('/points/import/commit', [PointImportController::class, 'commit'])->name('points.import.commit');
    });

    /**
     * ABM
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

            /**
             * Usuarios
             * IMPORTANTE: export va antes de users/{user}
             */
            Route::get('users/export', [UserController::class, 'export'])->name('users.export');

            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

            Route::resource('point-references', PointReferenceController::class)
                ->names('point-references');

            Route::middleware(['role:admin_sitio'])->group(function () {
                Route::resource('companies', CompanyController::class);
            });
        });

    /**
     * Ofertas
     */
    Route::resource('ofertas', OfertaController::class);
});

require __DIR__ . '/auth.php';
