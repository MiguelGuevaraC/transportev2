<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class MaintenanceFormAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'group_menu_id',
        'typeof_user_id',
        'allowed',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];

    const filters = [
        'name'            => 'like',
        'group_menu_id'   => '=',
        'typeof_user_id'  => '=',
        'allowed'         => '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function groupMenu()
    {
        return $this->belongsTo(GroupMenu::class, 'group_menu_id');
    }

    public function typeofUser()
    {
        return $this->belongsTo(Role::class, 'typeof_user_id');
    }
}
