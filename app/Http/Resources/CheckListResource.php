<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckListResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="CheckList",
     *     title="CheckList",
     *     description="Modelo que representa un CheckList",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="ID del CheckList"),
     *     @OA\Property(property="name", type="string", description="Nombre del CheckList"),
     *     @OA\Property(property="status", type="string", description="Estado del CheckList"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación del CheckList"),
     *     @OA\Property(property="checkListItems", type="array", @OA\Items(ref="#/components/schemas/CheckListItem"))
     * )
     */
    
    public function toArray($request): array
    {
        return [
            'id'              => $this->id ?? null,
            'numero'          => $this->numero ?? null,
            'date_check_list' => $this->date_check_list ?? null,
            'vehicle_id'      => $this->vehicle_id ?? null,
            'observation'     => $this->observation ?? null,
            'status'          => $this->status ?? null,
            'created_at'      => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            // Relación con los ítems, solo 'id' y 'name'
            'checkListItems'  => $this->checkListItems->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            }) ?? [],
        ];
    }
}
