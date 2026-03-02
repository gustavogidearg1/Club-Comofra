<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
  'name','email','password',
  'cuil','direccion','telefono',
  'company_id','pais_id','provincia_id','localidad_id',
  'fecha_nacimiento','activo',
  'imagen',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento'  => 'date',
        'activo'            => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class);
    }
    public function pointMovements()
    {
        return $this->hasMany(PointMovement::class, 'employee_user_id');
    }

    /** Movimientos creados por este usuario (admin/negocio) */
    public function createdPointMovements()
    {
        return $this->hasMany(PointMovement::class, 'created_by');
    }

    public function redemptionsAsEmployee()
{
    return $this->hasMany(\App\Models\PointRedemption::class, 'employee_user_id');
}

public function redemptionsAsBusiness()
{
    return $this->hasMany(\App\Models\PointRedemption::class, 'business_user_id');
}


}
