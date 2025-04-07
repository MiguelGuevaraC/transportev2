<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Category",
     *     title="Category",
     *     description="Model representing a Category",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Category ID"),
     *     @OA\Property(property="name", type="string", description="Name of the taller"),
     *     @OA\Property(property="status", type="string", description="Status of the taller"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the taller")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'         => $this->id ?? null,
            'name'       => $this->name ?? null,
            'status'     => $this->status ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
