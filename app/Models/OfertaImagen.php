<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfertaImagen extends Model
{
    protected $table = 'oferta_imagenes';

    protected $fillable = [
        'oferta_id',
        'ruta',
        'orden',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
    ];

    public function oferta()
    {
        return $this->belongsTo(Oferta::class);
    }
}
