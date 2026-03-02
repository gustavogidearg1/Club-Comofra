<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMovement extends Model
{
    protected $fillable = [
        'company_id',
        'employee_user_id',
        'business_user_id',
        'created_by',
        'confirmed_by',
        'batch_id',
        'type',
        'subtype',
        'points',
        'money_amount',
        'reference',   // ✅ texto
        'note',
        'occurred_at',
        'voided_at',
        'voided_by',
        'void_note',
    ];

    protected $casts = [
        'occurred_at'   => 'datetime',
        'money_amount'  => 'decimal:2',
        'voided_at'     => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    public function business()
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function batch()
    {
        return $this->belongsTo(PointImportBatch::class, 'batch_id');
    }


}
