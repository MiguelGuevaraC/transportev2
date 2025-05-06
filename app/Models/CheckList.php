<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckList extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'numero',
        'date_check_list',
        'vehicle_id',
        'observation',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [

        'numero'          => 'like',
        'date_check_list' => 'like',

        'vehicle_id'      => 'like',
        'observation'     => 'like',

        'status'          => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function checkListItems()
    {
        return $this->belongsToMany(CheckListItem::class, 'check_list_details', 'check_list_id', 'check_list_item_id')
            ->withPivot('observation', 'is_selected') // NECESARIO
            ->withTimestamps();
    }
    
}
