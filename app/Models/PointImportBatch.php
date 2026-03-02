<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointImportBatch extends Model
{
    protected $fillable = [
        'company_id','created_by','filename','status',
        'rows_total','rows_ok','rows_error',
    ];

    public function movements()
    {
        return $this->hasMany(PointMovement::class, 'batch_id');
    }
}
