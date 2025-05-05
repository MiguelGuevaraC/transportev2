<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Service",
     *     title="Service",
     *     description="Model representing a Service",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="Service ID"),
     *     @OA\Property(property="name", type="string", description="Name of the service"),
     *     @OA\Property(property="description", type="string", description="Description of the service"),
     *     @OA\Property(property="status", type="string", description="Status of the service"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the service")
     * )
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id ?? null,
            'name'        => $this->name ?? null,
            'description' => $this->description ?? null,
            'status'      => $this->status ?? null,
            'created_at'  => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
