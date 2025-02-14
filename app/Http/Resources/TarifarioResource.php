<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TarifarioResource extends JsonResource
{

/**
 * @OA\Schema(
 *     schema="Tarifario",
 *     title="Tarifario",
 *     description="Model representing a Tariff",
 *     required={"id", "tarifa", "unity_id"},
 *     
 *     @OA\Property(property="id", type="integer", description="Tariff ID"),
 *     @OA\Property(property="tarifa", type="number", format="float", description="Rate or tariff value"),
 *     @OA\Property(property="tarifa_camp", type="number", format="float", nullable=true, description="Campaign tariff value"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Description of the tariff"),
 *     @OA\Property(property="limitweight_min", type="number", format="float", description="Minimum weight limit"),
 *     @OA\Property(property="limitweight_max", type="number", format="float", description="Maximum weight limit"),
 *     @OA\Property(property="destination_id", type="integer", description="ID of the destination place"),
 *     @OA\Property(property="destination", ref="#/components/schemas/Place", description="Destination place object"),
 *     @OA\Property(property="origin_id", type="integer", description="ID of the origin place"),
 *     @OA\Property(property="origin", ref="#/components/schemas/Place", description="Origin place object"),
 *     @OA\Property(property="unity_id", type="integer", description="ID of the associated unity"),
 *     @OA\Property(property="unity", ref="#/components/schemas/Unity", description="Associated Unity object"),
 *     @OA\Property(property="person_id", type="integer", nullable=true, description="ID of the associated person"),
 *     @OA\Property(property="person", ref="#/components/schemas/Person", description="Associated Person object"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the tariff")
 * )
 */


    public function toArray($request): array
    {
        return [
            'id'          => $this->id ?? null,
            'tarifa'      => $this->tarifa ?? null,
            'description' => $this->description ?? null,
            'tarifa_camp'=> $this->tarifa_camp ?? null,
            'limitweight_min'=> $this->limitweight_min ?? null,
            'limitweight_max'=> $this->limitweight_max ?? null,
            'destination_id'=> $this->destination_id ?? null,
            'destination'=> $this->destination ?? null,
            'origin_id'=> $this->origin_id ?? null,
            'origin'=> $this->origin ?? null,
            'unity_id'    => $this->unity_id ?? null,
            'unity'       => $this->unity ?? null,
            'person_id'   => $this->person_id ?? null,
            'person'      => $this->person ? $this->person : null,
            'created_at'  => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
