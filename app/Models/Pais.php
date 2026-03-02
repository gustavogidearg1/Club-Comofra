<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
  protected $table = 'paises';

  protected $fillable = ['nombre','iso2','iso3'];

  public function provincias()
  {
    return $this->hasMany(Provincia::class);
  }
}
