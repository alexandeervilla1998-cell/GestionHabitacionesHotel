<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; // Pago no tiene updated_at

    protected $fillable = [
        'factura_id',
        'monto',
        'metodo_pago',
        'estado_pago',
        'creado_en'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'metodo_pago' => 'string',
        'estado_pago' => 'string',
        'creado_en' => 'datetime',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function scopeActivos($query)
    {
        return $query;
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado_pago', 'completado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_pago', 'pendiente');
    }

    public function estaCompletado()
    {
        return $this->estado_pago === 'completado';
    }

    public function estaPendiente()
    {
        return $this->estado_pago === 'pendiente';
    }

    public function estaFallido()
    {
        return $this->estado_pago === 'fallido';
    }

    public function estaReembolsado()
    {
        return $this->estado_pago === 'reembolsado';
    }

    public function marcarComoCompletado()
    {
        $this->estado_pago = 'completado';
        return $this;
    }

    public function marcarComoFallido()
    {
        $this->estado_pago = 'fallido';
        return $this;
    }

    public function marcarComoReembolsado()
    {
        $this->estado_pago = 'reembolsado';
        return $this;
    }
}
