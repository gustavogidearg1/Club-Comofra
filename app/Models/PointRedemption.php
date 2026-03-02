<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointRedemption extends Model
{
    protected $fillable = [
    'company_id',
    'employee_user_id',
    'business_user_id',
    'created_by',
    'point_movement_id',
    'points',
    'reference',
    'note',
    'status',
    'token',
    'expires_at',
    'confirmed_at',
    'confirmed_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(PointMovement::class, 'point_movement_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function confirmedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'confirmed_by');
}

public function settlement(): BelongsTo
{
  return $this->belongsTo(PointSettlement::class, 'settlement_id');
}

}
