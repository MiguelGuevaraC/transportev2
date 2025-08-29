<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DocAlmacenResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="correlativo", type="string"),
 *     @OA\Property(property="concept_id", type="integer"),
 *     @OA\Property(property="concept_name", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="movement_date", type="datetime"),
 *     @OA\Property(property="reference_id", type="integer"),
 *     @OA\Property(property="reference_type", type="string"),
 *     @OA\Property(property="note", type="string"),
 *     @OA\Property(property="details", type="array", @OA\Items(ref="#/components/schemas/DocAlmacenDetailResource")),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class DocAlmacenResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'correlativo' => str_pad($this->id ?? 0, 8, '0', STR_PAD_LEFT),
            'concept_id' => $this->concept_id ?? null,
            'concept_name' => $this->concept?->name ?? null,
            'user_id' => $this->user_id ?? null,
            'user_username' => $this->user?->username ?? null,
            'type' => $this->type ?? null,
           
            'movement_date' => $this->movement_date
        ? Carbon::parse($this->movement_date)->translatedFormat('d \d\e F \d\e Y, H:i')
        : null,
            'reference_id' => $this->reference_id ?? null,
            'reference_type' => $this->reference_type ?? null,
           
            'note' => $this->note ?? null,
            'details' => DocAlmacenDetailResource::collection($this->details),
            'details_list' => '<div style="max-height: 120px; overflow-y: auto;"><ul style="padding-left: 20px; margin: 0;">' .
                $this->details->map(
                    fn($d) =>
                    '<li style="margin-bottom: 4px;">' .
                    '<span style="color: green; font-weight: bold;">(Cant:' . e($d->quantity) . ')</span> ' .
                    '<span style="font-weight: 500;">' . e($d->tire->code) . '</span>' .
                    '</li>'
                )->implode('') .
                '</ul></div>',


            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
