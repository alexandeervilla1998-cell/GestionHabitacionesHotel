<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'numero_factura',
        'subtotal',
        'impuestos',
        'total',
        'fecha_emision',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_emision' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    public function scopeActivas($query)
    {
        return $query;
    }

    public function getTotalPagadoAttribute()
    {
        return (float) $this->pagos()
            ->where('estado_pago', 'completado')
            ->sum('monto');
    }

    public function getSaldoPendienteAttribute()
    {
        return max(0, (float) $this->total - (float) $this->total_pagado);
    }

    public function estaPagada()
    {
        return (float) $this->total_pagado >= (float) $this->total;
    }

    public function generarNumeroFactura()
    {
        $this->numero_factura = 'FAC-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        return $this;
    }
}
