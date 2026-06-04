<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'factura_id' => $this->factura_id,
            'monto' => (float) $this->monto,
            'metodo_pago' => $this->metodo_pago,
            'estado_pago' => $this->estado_pago,
            'creado_en' => $this->creado_en,
            'factura' => $this->when($this->relationLoaded('factura'), function () {
                return new FacturaResource($this->factura);
            }),
        ];
    }
}
