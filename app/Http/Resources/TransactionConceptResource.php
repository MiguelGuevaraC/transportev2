<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionConceptResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="TransactionConcept",
     *     title="TransactionConcept",
     *     description="Model representing a TransactionConcept",
     *     required={"id", "name"},
     *     @OA\Property(property="id", type="integer", description="TransactionConcept ID"),
     *     @OA\Property(property="name", type="string", description="Name of the Concept"),
     *     @OA\Property(property="type", type="string", description="Type of the Concept"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the unity")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'         => $this->id ?? null,
            'name'       => $this->name ?? null,
            'type'       => $this->type ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
