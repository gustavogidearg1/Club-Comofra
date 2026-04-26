<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Oferta extends Model
{
    use SoftDeletes;

    protected $table = 'ofertas';

    protected $fillable = [
        'company_id',
        'user_id',
        'titulo',
        'slug',
        'descripcion_corta',
        'descripcion',
        'observaciones',
        'precio',
        'precio_anterior',
        'fecha_desde',
        'fecha_hasta',
        'publicada',
        'destacada',
        'enviar_correo',
        'correo_enviado',
        'fecha_envio_correo',
        'estado',
        'orden',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_anterior' => 'decimal:2',
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'publicada' => 'boolean',
        'destacada' => 'boolean',
        'enviar_correo' => 'boolean',
        'correo_enviado' => 'boolean',
        'fecha_envio_correo' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($oferta) {
            if (empty($oferta->slug)) {
                $oferta->slug = static::generarSlugUnico($oferta->titulo);
            }
        });

        static::updating(function ($oferta) {
            if ($oferta->isDirty('titulo') && empty($oferta->slug)) {
                $oferta->slug = static::generarSlugUnico($oferta->titulo, $oferta->id);
            }
        });
    }

    public static function generarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $base = Str::slug($titulo);
        $slug = $base ?: 'oferta';
        $contador = 1;

        while (
            static::when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function imagenes()
    {
        return $this->hasMany(OfertaImagen::class)->orderBy('orden');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(OfertaImagen::class)->where('principal', true);
    }

    public function getEstaVigenteAttribute(): bool
    {
        $hoy = now()->startOfDay();

        $cumpleDesde = !$this->fecha_desde || $this->fecha_desde->startOfDay() <= $hoy;
        $cumpleHasta = !$this->fecha_hasta || $this->fecha_hasta->startOfDay() >= $hoy;

        return $this->publicada && $cumpleDesde && $cumpleHasta;
    }
}
