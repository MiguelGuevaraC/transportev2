<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TypeDocumentResource extends JsonResource
{

    

    /**
     * @OA\Schema(
     *     schema="TypeDocument",
     *     title="TypeDocument",
     *     description="Model representing a Type Document",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Type Document ID"),
     *     @OA\Property(property="name", type="string", description="Name of the Type Document"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the Type Document")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'         => $this->id ?? null,
            'name'       => $this->name ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
