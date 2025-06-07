<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray($request): array
    {
        $isConsolidated = $this->is_consolidated ?? null;

        // Obtener última recepción si existe
        $lastReception = $this->receptions ? collect($this->receptions)->last() : null;

        return [
            'id'                       => $this->id ?? null,
            'sequentialNumber'         => $this->sequentialNumber ?? null,
            'correlative'              => $this->correlative ?? null,
            'paymentDate'              => $this->paymentDate ?? null,
            'total'                    => $this->total ?? null,
            'yape'                     => $this->yape ?? null,
            'deposit'                  => $this->deposit ?? null,
            'cash'                     => $this->cash ?? null,
            'card'                     => $this->card ?? null,
            'plin'                     => $this->plin ?? null,
            'comment'                  => $this->comment ?? null,
            'monto_detraction'         => $this->monto_detraction ?? null,
            'monto_neto'               => $this->monto_neto ?? null,
            'value_ref'                => $this->value_ref ?? null,
            'percent_ref'              => $this->percent_ref ?? null,
            'isValue_ref'              => $this->isValue_ref ?? null,
            'nroTransferencia'         => $this->nroTransferencia ?? null,
            'productList'              => $this->productList ?? null,
            'saldo'                    => $this->saldo ?? null,
            'typeCaja'                 => $this->typeCaja ?? null,
            'operationNumber'          => $this->operationNumber ?? null,
            'typeDocument'             => $this->typeDocument ?? null,
            'typePayment'              => $this->typePayment ?? null,
            'typeSale'                 => $this->typeSale ?? null,
            'codeDetraction'           => $this->codeDetraction ?? null,
            'percentDetraction'        => $this->percentDetraction ?? null,
            'isBankPayment'            => $this->isBankPayment ?? null,
            'numberVoucher'            => $this->numberVoucher ?? null,
            'routeVoucher'             => $this->routeVoucher ?? null,
            'status'                   => $this->status ?? null,
            'getstatus_fact'           => $this->getstatus_fact ?? null,
            'status_facturado'         => $this->status_facturado ?? null,
            'movType'                  => $this->movType ?? null,
            'programming_id'           => $this->programming_id ?? null,
            'paymentConcept_id'        => $this->paymentConcept_id ?? null,
            'box_id'                   => $this->box_id ?? null,
            'pay_installment_id'       => $this->pay_installment_id ?? null,
            'bank_id'                  => $this->bank_id ?? null,
            'branchOffice_id'          => $this->branchOffice_id ?? null,
            'reception_id'             => $this->reception_id ?? null,
            'person_id'                => $this->person_id ?? null,
            'user_id'                  => $this->user_id ?? null,
            'user_edited_id'           => $this->user_edited_id ?? null,
            'user_deleted_id'          => $this->user_deleted_id ?? null,
            'user_factured_id'         => $this->user_factured_id ?? null,
            'mov_id'                   => $this->mov_id ?? null,
            'observation'              => $this->observation ?? null,
            'driverExpense_id'         => $this->driverExpense_id ?? null,
            'person_reception_id'      => $this->person_reception_id ?? null,
            'created_at'               => $this->created_at ?? null,

            // Relaciones
            'is_consolidated'          => $this->is_consolidated ?? null,
            'receptions'               => $this->receptions ?? null,
            'personreception'          => $this->personreception ?? null,
            'branch_office'            => $this->branchOffice ?? null,
            'payment_concept'          => $this->payment_concept ?? null,
            'box'                      => $this->box ?? null,
            'reception'                => $this->reception ?? null,

            'description_consolidated' => $isConsolidated && $lastReception ? $lastReception['description'] : null,

            'detalles'                 => $isConsolidated
            ? []
            : VentaConsolidatedDetalleResource::collection(collect($this->receptions ?? [])),
            'person'                   => $this->person ?? null,
            'bank'                     => $this->bank ?? null,
            'user'                     => $this->user ?? null,
            'mov_venta'                => $this->movVenta ?? null,
            'installments'             => $this->installments ?? null,
        ];
    }
}
