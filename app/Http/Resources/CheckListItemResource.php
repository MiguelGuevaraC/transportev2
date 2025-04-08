<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckListItemResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Check List Item",
     *     title="Check List Item",
     *     description="Model representing a Check List Item",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Check List Item ID"),
     *     @OA\Property(property="name", type="string", description="Name of the Check List Item"),
     *     @OA\Property(property="status", type="string", description="Status of the Check List Item"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the Check List Item")
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
