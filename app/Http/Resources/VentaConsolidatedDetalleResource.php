<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VentaConsolidatedDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        $originName          = strtoupper(optional($this['origin'])['name'] ?? 'ORIGEN DESCONOCIDO');
        $destinationName     = strtoupper(optional($this['destination'])['name'] ?? 'DESTINO DESCONOCIDO');
        $detailsDescriptions = implode(', ', collect($this['details'] ?? [])->pluck('description')->toArray());
        $comment             = $this['comment'] ?? '';

        $description = "SERVICIO DE TRANSPORTE DE $originName - $destinationName, CON TRASLADO DE $detailsDescriptions, $comment";

        return [
            'id'               => null,
            'moviment_id'      => $this['moviment_id'] ?? null,
            'carrier_guide_id' => optional($this['firstCarrierGuide'])['id'] ?? null,
            'reception_id'     => $this['id'] ?? null,
            'tract_id'         => null,
            'cantidad'         => '1',
            'precioVenta'      => $this['paymentAmount'] ?? null,
            'precioCompra'     => null,
            'description'      => $description,
            'guia'             => '-', // Puedes cambiar esto si tienes la guía como texto
            'os'               => '-',
            'placa'            => '-',
            'created_at'       => $this['created_at'] ?? null,
            'updated_at'       => $this['updated_at'] ?? null,
        ];
    }
}
