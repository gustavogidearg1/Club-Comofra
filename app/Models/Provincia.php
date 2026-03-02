<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
  protected $table = 'provincias';

  protected $fillable = ['pais_id','nombre'];

  public function pais()
  {
    return $this->belongsTo(Pais::class);
  }

  public function localidades()
  {
    return $this->hasMany(Localidad::class);
  }
}

