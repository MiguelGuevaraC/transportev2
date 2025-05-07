<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CargaDetailDocument",
 *     title="CargaDetailDocument",
 *     description="CargaDetail model",
 *     required={"id", "quantity", "product_id", "almacen_id", "seccion_id", "document_carga_id", "branchOffice_id"},
 *
 *     @OA\Property(property="id", type="integer", description="ID del detalle de carga"),
 *     @OA\Property(property="quantity", type="integer", description="Cantidad del producto"),
 *     @OA\Property(property="product", ref="#/components/schemas/Product", description="Producto relacionado"),
 *     @OA\Property(property="product_id", type="integer", description="ID del producto"),
 *     @OA\Property(property="almacen_id", type="integer", description="ID del almacén"),
 *     @OA\Property(property="almacen", ref="#/components/schemas/Almacen", description="Almacén relacionado"),
 *     @OA\Property(property="seccion_id", type="integer", description="ID de la sección"),
 *     @OA\Property(property="seccion", ref="#/components/schemas/Seccion", description="Sección relacionada"),
 *     @OA\Property(property="document_carga_id", type="integer", description="ID del documento de carga"),
 *     @OA\Property(property="document_carga", ref="#/components/schemas/DocumentCarga", description="Documento de carga relacionado"),
 *     @OA\Property(property="branchOffice_id", type="integer", description="ID de la sucursal"),
 *     @OA\Property(property="branchOffice", ref="#/components/schemas/BranchOffice", description="Sucursal relacionada"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario adicional"),
 *     @OA\Property(property="num_anexo", type="string", nullable=true, description="Número de anexo"),
 *     @OA\Property(property="date_expiration", type="string", format="date", nullable=true, description="Fecha de expiración"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación en formato YYYY-MM-DD HH:MM:SS")
 * )
 */

class CargaDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id ?? null,
            'quantity'          => $this->quantity ?? null,
            'product'           => $this->product ?? null,
            'product_id'        => $this->product_id ?? null,
            'almacen_id'        => $this->almacen_id ?? null,
            'almacen'           => $this->almacen ?? null,
            'seccion_id'        => $this->seccion_id ?? null,
            'seccion'           => $this->seccion ?? null,
            'document_carga_id' => $this->document_carga_id ?? null,
            'document_carga_code_doc'    => $this?->document_carga?->code_doc ?? null,
            'movement_date'    => $this?->document_carga?->movement_date ?? null,
            'branchOffice_id'   => $this->branchOffice_id ?? null,
            'branchOffice'      => $this->branchOffice ?? null,
            'comment'           => $this->comment ?? null,
            'num_anexo'         => $this->num_anexo ?? null,
            'date_expiration'   => $this->date_expiration ?? null,
            'created_at'        => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
