<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckListDetails extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'check_list_id',
        'check_list_item_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'check_list_id' => '=',
        'check_list_item_id' => '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function checkList()
    {
        return $this->belongsTo(CheckList::class, 'check_list_id');
    }

    // Relación: CheckListDetails pertenece a un CheckListItem
    public function checkListItem()
    {
        return $this->belongsTo(CheckListItem::class, 'check_list_item_id');
    }
  
}
