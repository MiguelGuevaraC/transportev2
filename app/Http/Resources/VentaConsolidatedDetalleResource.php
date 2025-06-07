<?php

use App\Models\Reception;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaConsolidatedDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        // Asegúrate de que $this es un modelo Eloquent (ej. Reception::find($id))
        $reception = Reception::find($this->id); // Usar el ID para obtener el modelo

        // Evita error si no se encuentra el modelo
        if (!$reception) {
            return [];
        }

        $originName = strtoupper(optional($reception->origin)->name ?? 'ORIGEN DESCONOCIDO');
        $destinationName = strtoupper(optional($reception->destination)->name ?? 'DESTINO DESCONOCIDO');
        $detailsDescriptions = implode(', ', optional($reception->details)->pluck('description')->toArray() ?? []);
        $comment = $reception->comment ?? '';

        $description = "SERVICIO DE TRANSPORTE DE $originName - $destinationName, CON TRASLADO DE $detailsDescriptions, $comment";

        return [
            'id'               => null,
            'moviment_id'      => $reception->moviment_id ?? null,
            'carrier_guide_id' => optional($reception->firstCarrierGuide)->id ?? null,
            'reception_id'     => $reception->id ?? null,
            'tract_id'         => null,
            'cantidad'         => '1',
            'precioVenta'      => $reception->paymentAmount ?? null,
            'precioCompra'     => null,
            'description'      => $description,
            'guia'             => '-', // Puedes cambiar esto si tienes la guía como texto
            'os'               => '-',
            'placa'            => '-',
            'created_at'       => $reception->created_at ?? null,
            'updated_at'       => $reception->updated_at ?? null,
        ];
    }
}
