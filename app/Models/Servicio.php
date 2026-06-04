<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'precio',
        'activo'
    ];

    protected $casts = [
        'precio' => 'float',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function reservaServicios()
    {
        return $this->hasMany(ReservaServicio::class, 'servicio_id');
    }

    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reserva_servicio', 'servicio_id', 'reserva_id')
                    ->withPivot(['cantidad', 'precio_unitario', 'subtotal', 'activo']);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
