<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleReserva extends Model
{
    use HasFactory;

    protected $table = 'detalle_reservas';
    
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'habitacion_id',
        'noches',
        'precio_noche',
        'subtotal',
        'activo'
    ];

    protected $casts = [
        'noches' => 'integer',
        'precio_noche' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function calcularSubtotal()
    {
        $this->subtotal = $this->noches * $this->precio_noche;
        return $this;
    }
}
