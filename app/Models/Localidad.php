<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
  protected $table = 'localidades';

  protected $fillable = ['provincia_id','nombre','cp'];

  public function provincia()
  {
    return $this->belongsTo(Provincia::class);
  }
}
