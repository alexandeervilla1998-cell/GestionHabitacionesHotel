<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HabitacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'tipo' => $this->tipo,
            'precio_por_noche' => (float) $this->precio_por_noche,
            'estado' => $this->estado,
            'activo' => $this->activo,
            'imagen' => $this->imagen,
            'creado_en' => $this->creado_en,
            'actualizado_en' => $this->actualizado_en,
            'disponible' => $this->estaDisponible(),
            'detalle_reservas_count' => $this->whenCounted('detalleReservas'),
            'detalle_reservas' => $this->when($this->relationLoaded('detalleReservas'), function () {
                return DetalleReservaResource::collection($this->detalleReservas);
            }),
        ];
    }
}
