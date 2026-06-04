<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    use HasFactory;

    protected $table = 'habitaciones';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'numero',
        'tipo',
        'precio_por_noche',
        'estado',
        'activo',
        'imagen',
        'creado_en',
        'actualizado_en'
    ];

    protected $casts = [
        'precio_por_noche' => 'decimal:2',
        'activo' => 'boolean',
        'tipo' => 'string',
        'estado' => 'string',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function detalleReservas()
    {
        return $this->hasMany(DetalleReserva::class, 'habitacion_id');
    }

    public function reservas()
    {
        return $this->hasManyThrough(Reserva::class, DetalleReserva::class, 'habitacion_id', 'id', 'id', 'reserva_id');
    }

    public function estaDisponible()
    {
        return $this->estado === 'disponible' && $this->activo;
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible')->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
