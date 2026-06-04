<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'numero_factura' => $this->numero_factura,
            'subtotal' => (float) $this->subtotal,
            'impuestos' => (float) $this->impuestos,
            'total' => (float) $this->total,
            'fecha_emision' => $this->fecha_emision,
            'total_pagado' => (float) $this->total_pagado,
            'saldo_pendiente' => (float) $this->saldo_pendiente,
            'esta_pagada' => $this->estaPagada(),
            'reserva' => $this->when($this->relationLoaded('reserva'), function () {
                return new ReservaResource($this->reserva);
            }),
            'pagos' => $this->when($this->relationLoaded('pagos'), function () {
                return PagoResource::collection($this->pagos);
            }),
        ];
    }
}
