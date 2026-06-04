<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'cliente_id',
        'fecha_entrada',
        'fecha_salida',
        'estado',
        'activo',
        'creado_en',
        'actualizado_en'
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
        'estado' => 'string',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalleReservas()
    {
        return $this->hasMany(DetalleReserva::class, 'reserva_id');
    }

    public function habitaciones()
    {
        return $this->belongsToMany(Habitacion::class, 'detalle_reservas', 'reserva_id', 'habitacion_id')
                    ->withPivot(['noches', 'precio_noche', 'subtotal', 'activo']);
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'reserva_servicio', 'reserva_id', 'servicio_id')
                    ->withPivot(['cantidad', 'precio_unitario', 'subtotal', 'activo']);
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'reserva_id');
    }

    public function getNochesAttribute()
    {
        return $this->fecha_entrada->diffInDays($this->fecha_salida);
    }

    public function getSubtotalHabitacionesAttribute(): float
    {
        return (float) $this->detalleReservas->sum(fn ($detalle) => (float) $detalle->subtotal);
    }

    public function getSubtotalServiciosAttribute(): float
    {
        return (float) $this->servicios->sum(fn ($servicio) => (float) $servicio->pivot->subtotal);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal_habitaciones + $this->subtotal_servicios;
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmada');
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }
}
