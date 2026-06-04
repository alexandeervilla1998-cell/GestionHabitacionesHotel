<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'fecha_entrada' => $this->fecha_entrada,
            'fecha_salida' => $this->fecha_salida,
            'estado' => $this->estado,
            'activo' => $this->activo,
            'creado_en' => $this->creado_en,
            'actualizado_en' => $this->actualizado_en,
            'noches' => $this->noches,
            'subtotal_habitaciones' => (float) $this->subtotal_habitaciones,
            'subtotal_servicios' => (float) $this->subtotal_servicios,
            'total' => (float) $this->total,
            'usuario' => $this->when($this->relationLoaded('usuario'), function () {
                return new UsuarioResource($this->usuario);
            }),
            'detalle_reservas' => $this->when($this->relationLoaded('detalleReservas'), function () {
                return DetalleReservaResource::collection($this->detalleReservas);
            }),
            'habitaciones' => $this->when($this->relationLoaded('habitaciones'), function () {
                return HabitacionResource::collection($this->habitaciones);
            }),
            'servicios' => $this->when($this->relationLoaded('servicios'), function () {
                return ServicioResource::collection($this->servicios);
            }),
            'factura' => $this->when($this->relationLoaded('factura'), function () {
                return new FacturaResource($this->factura);
            }),
        ];
    }
}
