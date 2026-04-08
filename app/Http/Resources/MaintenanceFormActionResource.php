<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceFormActionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'group_menu_id'    => $this->group_menu_id,
            'group_menu_name'  => $this->groupMenu?->name,
            'typeof_user_id'   => $this->typeof_user_id,
            'typeof_user_name' => $this->typeofUser?->name,
            'allowed'          => (bool) $this->allowed,
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
