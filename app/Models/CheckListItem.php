<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckListItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'address',
        'repuesto_id',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'   => 'like',
        'status' => 'like',
        'address' => 'like'
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function checkLists()
    {
        return $this->belongsToMany(CheckList::class, 'check_list_details', 'check_list_item_id', 'check_list_id')
                    ->withPivot('id', 'observation', 'is_selected')
                    ->withTimestamps();
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
