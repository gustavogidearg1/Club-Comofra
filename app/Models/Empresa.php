<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'company_id','name','cuit','email','telefono','direccion','logo',
        'nivel','contacto','observacion'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

