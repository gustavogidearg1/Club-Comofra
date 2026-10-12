<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sector extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'calidad',
        'active',
    ];

    protected $casts = [
        'calidad' => 'boolean',
        'active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['principal', 'active'])
            ->withTimestamps();
    }
}
