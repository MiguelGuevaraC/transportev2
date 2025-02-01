<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CargaResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="CargaDocument",
     *     title="CargaDocument",
     *     description="Carga model",
     *     required={"id", "movement_date", "quantity", "unit_price", "total_cost", "movement_type", "stock_balance", "product_id", "person_id"},
     *     @OA\Property(property="id", type="integer", description="Carga ID"),
     *     @OA\Property(property="movement_date", type="string", format="date", description="Date of movement"),
     *     @OA\Property(property="quantity", type="integer", description="Quantity of product moved"),
     *     @OA\Property(property="unit_price", type="number", format="float", description="Unit price of the product"),
     *     @OA\Property(property="total_cost", type="number", format="float", description="Total cost of the movement"),
     *     @OA\Property(property="weight", type="number", format="float", nullable=true, description="Weight of the product"),
     *     @OA\Property(property="movement_type", type="string", description="Type of movement (e.g., IN, OUT)"),
     *     @OA\Property(property="stock_balance", type="integer", description="Stock balance after movement"),
     *     @OA\Property(property="comment", type="string", nullable=true, description="Additional comments"),
     *     @OA\Property(property="product_id", type="integer", description="ID of related product"),
     *     @OA\Property(property="person_id", type="integer", description="ID of responsible person"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id ?? null,
            'movement_date' => $this->movement_date ?? null,
            'quantity'      => $this->quantity ?? null,
            'unit_price'    => $this->unit_price ?? null,
            'total_cost'    => $this->total_cost ?? null,
            'weight'        => $this->weight ?? null,
            'movement_type' => $this->movement_type ?? null,
            'stock_balance' => $this->stock_balance ?? null,
            'comment'       => $this->comment ?? null,
            'product_id'    => $this->product_id ?? null,
            'person_id'     => $this->person_id ?? null,
            'created_at'    => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
