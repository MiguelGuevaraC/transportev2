<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNote extends Model
{
    use SoftDeletes;

    protected $table = 'debit_notes';

    public const REASON_CODES_SUNAT_10 = [
        '01' => 'Intereses por mora',
        '02' => 'Aumento en el valor',
        '03' => 'Penalidades',
    ];

    protected $fillable = [
        'id',
        'number',
        'reason',
        'reason_code',
        'total',
        'totalReferido',
        'newDate',
        'newTotal',
        'totalAjuste',
        'fechaAjuste',
        'comment',
        'description',
        'productList',
        'status',
        'status_facturado',
        'getstatus_fact',
        'percentaje',
        'state',
        'moviment_id',
        'branchOffice_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'reason_code_label',
    ];

    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

    public function getReasonCodeLabelAttribute(): ?string
    {
        if (! $this->reason_code) {
            return null;
        }

        return self::REASON_CODES_SUNAT_10[$this->reason_code] ?? null;
    }
}
