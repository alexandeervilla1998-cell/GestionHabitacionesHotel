<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaServicio extends Model
{
    use HasFactory;

    protected $table = 'reserva_servicio';
    
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'servicio_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'activo'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function calcularSubtotal()
    {
        $this->subtotal = $this->cantidad * $this->precio_unitario;
        return $this;
    }
}
