<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio' => (float) $this->precio,
            'activo' => $this->activo,
            'reserva_servicios_count' => $this->whenCounted('reservaServicios'),
            'reserva_servicios' => $this->when($this->relationLoaded('reservaServicios'), function () {
                return ReservaServicioResource::collection($this->reservaServicios);
            }),
        ];
    }
}
