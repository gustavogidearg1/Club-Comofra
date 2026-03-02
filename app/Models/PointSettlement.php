<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointSettlement extends Model
{
    protected $fillable = [
        'company_id',
        'business_user_id',
        'period_from',
        'period_to',
        'total_points',
        'total_amount',
        'status',         // draft | invoiced
        'note',
        'invoice_number',
        'invoiced_at',
        'invoiced_by',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to'   => 'date',
        'invoiced_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function invoicedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PointRedemption::class, 'settlement_id');
    }
}
