<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $paises      = \App\Models\Pais::query()->orderBy('nombre')->get();
        $provincias  = \App\Models\Provincia::query()->orderBy('nombre')->get();
        $localidades = \App\Models\Localidad::query()->orderBy('nombre')->get();

        return view('profile.edit', compact('user','paises','provincias','localidades'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Normalizar
        $request->merge([
            'cuil' => preg_replace('/\D+/', '', (string) $request->input('cuil')),
            'telefono' => $request->filled('telefono')
                ? preg_replace('/[^\d+]/', '', (string) $request->input('telefono'))
                : null,
        ]);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'cuil' => ['required','string','max:13'],
            'direccion' => ['nullable','string','max:255'],
            'telefono' => ['nullable','string','max:30'],
            'pais_id' => ['nullable','integer','exists:paises,id'],
            'provincia_id' => ['nullable','integer','exists:provincias,id'],
            'localidad_id' => ['nullable','integer','exists:localidades,id'],
            'fecha_nacimiento' => ['nullable','date'],
            'imagen' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $user->fill($data);

        // Imagen (hosting sin symlink)
        if ($request->hasFile('imagen')) {

            // borrar anterior (opcional)
            if ($user->imagen) {
                @unlink(storage_path('app/public/' . $user->imagen));
                @unlink(public_path('storage/' . $user->imagen));
            }

            $path = $request->file('imagen')->store('users', 'public');

            $from = storage_path('app/public/' . $path);
            $to   = public_path('storage/' . $path);

            File::ensureDirectoryExists(dirname($to));
            File::copy($from, $to);

            $user->imagen = $path;
        }

        $user->save();

        return back()->with('status', 'Perfil actualizado.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required','string','min:6','confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('status', 'Contraseña actualizada.');
    }

    public function destroy(Request $request)
    {
        // Si no querés permitir borrar cuenta, podés bloquearlo:
        // abort(403);

        $user = $request->user();

        // (opcional) borrar imagen
        if ($user->imagen) {
            @unlink(storage_path('app/public/' . $user->imagen));
            @unlink(public_path('storage/' . $user->imagen));
        }

        auth()->logout();

        $user->delete();

        return redirect()->route('login')->with('status', 'Cuenta eliminada.');
    }
}
