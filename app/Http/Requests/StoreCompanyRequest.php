<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required','string','max:255'],
            'cuit'            => ['nullable','string','max:13', 'unique:companies,cuit'],
            'email'           => ['nullable','email','max:255'],
            'telefono'        => ['nullable','string','max:255'],
            'direccion'       => ['nullable','string','max:255'],
            'logo'            => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'color_primario'  => ['nullable','string','max:255'],
            'color_secundario'=> ['nullable','string','max:255'],
        ];
    }
}
