<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required','string','max:255'],
            'cuit'        => ['nullable','string','max:13'],
            'email'       => ['nullable','email','max:255'],
            'telefono'    => ['nullable','string','max:50'],
            'direccion'   => ['nullable','string','max:255'],
            'logo'        => ['nullable','image','max:2048'],
            'nivel'       => ['required','integer','min:1','max:999'],
            'contacto'    => ['nullable','string','max:255'],
            'observacion' => ['nullable','string','max:5000'],
        ];
    }
}

