<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\OfertaImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Jobs\EnviarOfertaPublicadaJob;

class OfertaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Oferta::with(['company', 'user', 'imagenes']);

        if ($user->hasRole('admin_sitio')) {
            // ve todo
        } elseif ($user->hasRole('admin_empresa') || $user->hasRole('empleado')) {
            // ven todas las ofertas de su empresa
            $query->where('company_id', $user->company_id);
        } elseif ($user->hasRole('negocio')) {
            // ve solo sus propias ofertas
            $query->where('user_id', $user->id);
        } else {
            // por seguridad, no ve nada
            $query->whereRaw('1 = 0');
        }

        $ofertas = $query->orderByDesc('id')->paginate(12);

        return view('ofertas.index', compact('ofertas'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!($user->hasRole('admin_sitio') || $user->hasRole('admin_empresa') || $user->hasRole('negocio'))) {
            abort(403, 'No tenés permiso para crear ofertas.');
        }

        return view('ofertas.create');
    }

    public function store(Request $request)
{
    $user = Auth::user();

    if (!($user->hasRole('admin_sitio') || $user->hasRole('admin_empresa') || $user->hasRole('negocio'))) {
        abort(403, 'No tenés permiso para crear ofertas.');
    }

    $request->validate([
        'titulo'              => 'required|string|max:255',
        'descripcion_corta'   => 'nullable|string|max:255',
        'descripcion'         => 'nullable|string',
        'observaciones'       => 'nullable|string',
        'precio'              => 'nullable|numeric|min:0',
        'precio_anterior'     => 'nullable|numeric|min:0',
        'fecha_desde'         => 'nullable|date',
        'fecha_hasta'         => 'nullable|date|after_or_equal:fecha_desde',
        'destacada'           => 'nullable|boolean',
        'enviar_correo'       => 'nullable|boolean',
        'estado'              => 'required|string|in:borrador,publicada,pausada,vencida',
        'orden'               => 'nullable|integer|min:0',
        'imagenes'            => 'required|array|min:1',
        'imagenes.*'          => 'image|mimes:jpg,jpeg,png,webp|max:4096',
    ], [
        'imagenes.required' => 'Debés cargar al menos una imagen.',
        'imagenes.min'      => 'Debés cargar al menos una imagen.',
    ]);

    DB::beginTransaction();

    try {
        $oferta = Oferta::create([
            'company_id'         => $user->company_id,
            'user_id'            => $user->id,
            'titulo'             => $request->titulo,
            'descripcion_corta'  => $request->descripcion_corta,
            'descripcion'        => $request->descripcion,
            'observaciones'      => $request->observaciones,
            'precio'             => $request->precio,
            'precio_anterior'    => $request->precio_anterior,
            'fecha_desde'        => $request->fecha_desde,
            'fecha_hasta'        => $request->fecha_hasta,
            'publicada'          => $request->estado === 'publicada',
            'destacada'          => $request->boolean('destacada'),
            'enviar_correo'      => $request->boolean('enviar_correo'),
            'correo_enviado'     => false,
            'fecha_envio_correo' => null,
            'estado'             => $request->estado,
            'orden'              => $request->orden ?? 0,
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $ruta = $imagen->store('ofertas', 'public');

                \App\Models\OfertaImagen::create([
                    'oferta_id' => $oferta->id,
                    'ruta'      => $ruta,
                    'orden'     => $index,
                    'principal' => $index === 0,
                ]);
            }
        }

        DB::commit();

        if ($oferta->enviar_correo && $oferta->publicada && $oferta->estado === 'publicada') {
            EnviarOfertaPublicadaJob::dispatch($oferta->id);
        }

        return redirect()
            ->route('ofertas.index')
            ->with('success', 'Oferta creada correctamente.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Ocurrió un error al guardar la oferta: ' . $e->getMessage());
    }
}

    public function show(Oferta $oferta)
    {
        $this->autorizarVer($oferta);

        $oferta->load(['company', 'user', 'imagenes']);

        return view('ofertas.show', compact('oferta'));
    }

    public function edit(Oferta $oferta)
    {
        $this->autorizarModificar($oferta);

        $oferta->load('imagenes');

        return view('ofertas.edit', compact('oferta'));
    }

    public function update(Request $request, Oferta $oferta)
    {
        $this->autorizarModificar($oferta);

        $request->validate([
            'titulo'              => 'required|string|max:255',
            'descripcion_corta'   => 'nullable|string|max:255',
            'descripcion'         => 'nullable|string',
            'observaciones'       => 'nullable|string',
            'precio'              => 'nullable|numeric|min:0',
            'precio_anterior'     => 'nullable|numeric|min:0',
            'fecha_desde'         => 'nullable|date',
            'fecha_hasta'         => 'nullable|date|after_or_equal:fecha_desde',
            'destacada'           => 'nullable|boolean',
            'enviar_correo'       => 'nullable|boolean',
            'estado'              => 'required|string|in:borrador,publicada,pausada,vencida',
            'orden'               => 'nullable|integer|min:0',
            'imagenes_nuevas.*'   => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'eliminar_imagenes'   => 'nullable|array',
            'eliminar_imagenes.*' => 'integer|exists:oferta_imagenes,id',
        ]);

        DB::beginTransaction();

        try {
            $oferta->update([
                'titulo'             => $request->titulo,
                'descripcion_corta'  => $request->descripcion_corta,
                'descripcion'        => $request->descripcion,
                'observaciones'      => $request->observaciones,
                'precio'             => $request->precio,
                'precio_anterior'    => $request->precio_anterior,
                'fecha_desde'        => $request->fecha_desde,
                'fecha_hasta'        => $request->fecha_hasta,
                'publicada'          => $request->estado === 'publicada',
                'destacada'          => $request->boolean('destacada'),
                'enviar_correo'      => $request->boolean('enviar_correo'),
                'estado'             => $request->estado,
                'orden'              => $request->orden ?? 0,
            ]);

            if ($request->filled('eliminar_imagenes')) {
                $imagenesEliminar = OfertaImagen::where('oferta_id', $oferta->id)
                    ->whereIn('id', $request->eliminar_imagenes)
                    ->get();

                foreach ($imagenesEliminar as $imagen) {
                    if (Storage::disk('public')->exists($imagen->ruta)) {
                        Storage::disk('public')->delete($imagen->ruta);
                    }

                    $imagen->delete();
                }
            }

            if ($request->hasFile('imagenes_nuevas')) {
                $maxOrden = OfertaImagen::where('oferta_id', $oferta->id)->max('orden');
                $siguienteOrden = is_null($maxOrden) ? 0 : ($maxOrden + 1);

                foreach ($request->file('imagenes_nuevas') as $index => $imagen) {
                    $ruta = $imagen->store('ofertas', 'public');

                    OfertaImagen::create([
                        'oferta_id' => $oferta->id,
                        'ruta'      => $ruta,
                        'orden'     => $siguienteOrden + $index,
                        'principal' => false,
                    ]);
                }
            }

            $imagenesRestantes = OfertaImagen::where('oferta_id', $oferta->id)
                ->orderBy('orden')
                ->get();

            if ($imagenesRestantes->isNotEmpty() && !$imagenesRestantes->contains('principal', true)) {
                $imagenesRestantes->first()->update(['principal' => true]);
            }

            DB::commit();

            return redirect()
                ->route('ofertas.index')
                ->with('success', 'Oferta actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar la oferta: ' . $e->getMessage());
        }
    }

    public function destroy(Oferta $oferta)
{
    $this->autorizarModificar($oferta);

    DB::beginTransaction();

    try {
        $oferta->load('imagenes');

        foreach ($oferta->imagenes as $imagen) {
            if (Storage::disk('public')->exists($imagen->ruta)) {
                Storage::disk('public')->delete($imagen->ruta);
            }

            $imagen->delete();
        }

        $oferta->forceDelete();

        DB::commit();

        return redirect()
            ->route('ofertas.index')
            ->with('success', 'Oferta eliminada correctamente.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->with('error', 'No se pudo eliminar la oferta: ' . $e->getMessage());
    }
}

    private function autorizarVer(Oferta $oferta): void
    {
        $user = Auth::user();

        if ($user->hasRole('admin_sitio')) {
            return;
        }

        if ($user->hasRole('admin_empresa') || $user->hasRole('empleado')) {
            if ((int) $oferta->company_id === (int) $user->company_id) {
                return;
            }
        }

        if ($user->hasRole('negocio')) {
            if ((int) $oferta->user_id === (int) $user->id) {
                return;
            }
        }

        abort(403, 'No tenés permiso para acceder a esta oferta.');
    }

    private function autorizarModificar(Oferta $oferta): void
    {
        $user = Auth::user();

        if ($user->hasRole('admin_sitio')) {
            return;
        }

        if ($user->hasRole('admin_empresa')) {
            if ((int) $oferta->company_id === (int) $user->company_id) {
                return;
            }
        }

        if ($user->hasRole('negocio')) {
            if ((int) $oferta->user_id === (int) $user->id) {
                return;
            }
        }

        abort(403, 'No tenés permiso para modificar esta oferta.');
    }



}
