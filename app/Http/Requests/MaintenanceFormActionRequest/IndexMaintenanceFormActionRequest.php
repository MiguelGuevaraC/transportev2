<?php

namespace App\Http\Requests\MaintenanceFormActionRequest;

use App\Http\Requests\IndexRequest;

class IndexMaintenanceFormActionRequest extends IndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name'            => 'nullable|string',
            'group_menu_id'   => 'nullable|integer',
            'typeof_user_id'  => 'nullable|integer',
            'allowed'         => 'nullable|boolean',
        ]);
    }
}
