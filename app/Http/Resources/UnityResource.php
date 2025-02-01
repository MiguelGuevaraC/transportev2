<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnityResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Unity",
     *     title="Unity",
     *     description="Model representing a Unity",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Unity ID"),
     *     @OA\Property(property="name", type="string", description="Name of the unity"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the unity")
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
