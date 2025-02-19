<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Bank",
     *     title="Bank",
     *     description="Model representing a Bank",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Bank ID"),
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
