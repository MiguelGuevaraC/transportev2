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
 *
 *     @OA\Property(property="id", type="integer", description="Carga ID"),
 *     @OA\Property(property="movement_date", type="string", format="date", description="Date of movement"),
 *     @OA\Property(property="quantity", type="integer", description="Quantity of product moved"),
 *     @OA\Property(property="unit_price", type="number", format="float", description="Unit price of the product"),
 *     @OA\Property(property="total_cost", type="number", format="float", description="Total cost of the movement"),
 *     @OA\Property(property="weight", type="number", format="float", nullable=true, description="Weight of the product"),
 *     @OA\Property(property="movement_type", type="string", description="Type of movement (e.g., IN, OUT)"),
 *     @OA\Property(property="stock_balance_before", type="integer", description="Stock balance before movement"),
 *     @OA\Property(property="stock_balance_after", type="integer", description="Stock balance after movement"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Additional comments"),
 *     @OA\Property(property="product_id", type="integer", description="ID of related product"),
 *     @OA\Property(property="product", ref="#/components/schemas/Product"),
 *     @OA\Property(property="person_id", type="integer", description="ID of responsible person"),
 *     @OA\Property(property="person", ref="#/components/schemas/Person"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
 * )
 */

    public function toArray($request): array
    {
        return [
            'id'                   => $this->id ?? null,
            'movement_date'        => $this->movement_date ?? null,
            'quantity'             => $this->quantity ?? null,
            'unit_price'           => $this->unit_price ?? null,
            'total_cost'           => $this->total_cost ?? null,
            'weight'               => $this->weight ?? null,

            'lote_doc'             => $this->lote_doc ?? null,
            'code_doc'             => $this->code_doc ?? null,
            'date_expiration'      => $this->date_expiration ?? null,
            'num_anexo'            => $this->num_anexo ?? null,

            'movement_type'        => $this->movement_type ?? null,
            'stock_balance_before' => $this->stock_balance_before ?? null,
            'stock_balance_after'  => $this->stock_balance_after ?? null,
            'comment'              => $this->comment ?? null,
            'product_id'           => $this->product_id ?? null,
            'branchOffice_id'           => $this->branchOffice_id ?? null,
            'product'              => $this->product ? new ProductResource($this->product) : null,
            'person_id'            => $this->person_id ?? null,
            'person'               => $this->person ? new PersonaResource($this->person) : null,

            'distribuidor_id'            => $this->distribuidor_id ?? null,
            'distribuidor'               => $this->distribuidor ? new PersonaResource($this->distribuidor) : null,

            'created_at'           => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
