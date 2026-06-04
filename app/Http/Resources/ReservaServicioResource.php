<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaServicioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'servicio_id' => $this->servicio_id,
            'cantidad' => $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'subtotal' => (float) $this->subtotal,
            'activo' => $this->activo,
            'reserva' => $this->when($this->relationLoaded('reserva'), function () {
                return new ReservaResource($this->reserva);
            }),
            'servicio' => $this->when($this->relationLoaded('servicio'), function () {
                return new ServicioResource($this->servicio);
            }),
        ];
    }
}
