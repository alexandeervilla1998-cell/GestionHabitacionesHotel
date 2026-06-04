<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'habitacion_id' => $this->habitacion_id,
            'noches' => $this->noches,
            'precio_noche' => (float) $this->precio_noche,
            'subtotal' => (float) $this->subtotal,
            'activo' => $this->activo,
            'reserva' => $this->when($this->relationLoaded('reserva'), function () {
                return new ReservaResource($this->reserva);
            }),
            'habitacion' => $this->when($this->relationLoaded('habitacion'), function () {
                return new HabitacionResource($this->habitacion);
            }),
        ];
    }
}
