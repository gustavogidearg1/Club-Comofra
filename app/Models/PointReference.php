<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PointReference extends Model
{
    protected $fillable = [
        'company_id',
        'created_by_user_id',
        'updated_by_user_id',
        'name',
        'is_active',
        'sort_order',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForCompany(Builder $q, ?int $companyId): Builder
    {
        // company_id NULL = referencia global (todas)
        return $q->where(function($qq) use ($companyId){
            $qq->whereNull('company_id');
            if ($companyId) $qq->orWhere('company_id', $companyId);
        });
    }
}
