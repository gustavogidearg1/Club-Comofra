<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name','cuit','email','telefono','direccion','logo',
        'color_primario','color_secundario'
    ];


}
