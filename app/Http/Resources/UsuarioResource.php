<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'rol' => $this->rol,
            'activo' => $this->activo,
            'creado_en' => $this->creado_en,
            'actualizado_en' => $this->actualizado_en,
            'reservas_count' => $this->whenCounted('reservas'),
            'reservas' => $this->when($this->relationLoaded('reservas'), function () {
                return ReservaResource::collection($this->reservas);
            }),
        ];
    }
}
