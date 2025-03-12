<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BankAccount extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'id',
        'bank_id',
        'bank_account_id',
        'account_number',
        'account_type',
        'currency',
        'balance',
        'holder_name',
        'holder_id',
        'status',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'bank_id'        => '=',
        'account_number' => 'like',
        'account_type'   => 'like',
        'currency'       => 'like',
        'balance'        => 'like',
        'holder_name'    => 'like',
        'holder_id'      => '=',
        'status'         => 'like',
    ];

    const fields_export = [
        'Número de Cuenta'   => 'account_number',
        'Banco'              => 'bank.name',
        'Moneda'             => 'currency',
        'Monto'              => 'balance',
        'Estado'             => 'status',
    ];    const sorts = [
        'id' => 'desc',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function holder()
    {
        return $this->belongsTo(Person::class, 'holder_id');
    }

    public function updateBalance()
    {
        $balance = DB::table('bank_movements')
            ->where('bank_account_id', $this->id)
            ->whereIn('type_moviment', ['ENTRADA', 'SALIDA'])
            ->whereNull('deleted_at')
            ->sum(DB::raw("IF(type_moviment = 'ENTRADA', total_moviment, -total_moviment)"));

        $this->update(['balance' => $balance ?? 0]);
    }

}
