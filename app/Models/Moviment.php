<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Moviment extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="MovimentRequest",
     *     title="moviment",
     *     description="Movimiento",
     *     required={"id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del moviment"
     *     ),
     *     @OA\Property(
     *         property="sequentialNumber",
     *         type="string",
     *         description="Número secuencial",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="paymentDate",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de pago",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="total",
     *         type="number",
     *         format="decimal",
     *         description="Total",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="yape",
     *         type="number",
     *         format="decimal",
     *         description="Pago por Yape",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="deposit",
     *         type="number",
     *         format="decimal",
     *         description="Depósito",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="cash",
     *         type="number",
     *         format="decimal",
     *         description="Efectivo",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="card",
     *         type="number",
     *         format="decimal",
     *         description="Pago por tarjeta",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="plin",
     *         type="number",
     *         format="decimal",
     *         description="Pago por tarjeta",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="comment",
     *         type="string",
     *         description="Comentario",
     *         nullable=true
     *     ),
     *          @OA\Property(
     *         property="movType",
     *         type="string",
     *         description="Tipo Movimiento",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="typeDocument",
     *         type="string",
     *         description="Tipo de documento",
     *         nullable=true
     *     ),
     *      *        @OA\Property(
     *         property="isBankPayment",
     *         type="boolean",
     *         description="Tipo de documento",
     *         nullable=true
     *     ),
     *        @OA\Property(
     *         property="numberVoucher",
     *         type="string",
     *         description="Tipo de documento",
     *         nullable=true
     *     ),
     *        @OA\Property(
     *         property="routeVoucher",
     *         type="string",
     *         description="Tipo de documento",
     *         nullable=true
     *     ),

     *     @OA\Property(
     *         property="typePayment",
     *         type="string",
     *         description="Tipo de pago",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="typeSale",
     *         type="string",
     *         description="Tipo de venta",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Estado",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="programming_id",
     *         type="integer",
     *         description="ID de programación",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="paymentConcept_id",
     *         type="integer",
     *         description="ID del concepto de pago",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="box_id",
     *         type="integer",
     *         description="ID de la caja",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="branchOffice_id",
     *         type="integer",
     *         description="ID de la sucursal",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="reception_id",
     *         type="integer",
     *         description="ID de la recepción"
     *     ),
     *     @OA\Property(
     *         property="person_id",
     *         type="integer",
     *         description="ID de la persona",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         description="ID del usuario",
     *         nullable=true
     *     )
     * );
     */

    protected $fillable = [
        'sequentialNumber',
        'correlative',
        'paymentDate',
        'total',
        'yape',
        'deposit',
        'cash',
        'card',
        'plin',
        'comment',
        'typeDocument',
        'typePayment',
        'typeSale',
        'nroTransferencia',
        'codeDetraction',
        'percentDetraction',

        'monto_detraction',
        'monto_neto',
        'value_ref',
        'percent_ref',
        'isValue_ref',

        'isBankPayment',
        'numberVoucher',
        'routeVoucher',

        'productList',
        'saldo',
        'movType',
        'typeCaja',
        'operationNumber',

        'status',
        'status_facturado',
        'getstatus_fact',
        'pay_installment_id',
        'user_edited_id',
        'user_deleted_id',
        'user_factured_id',

        'created_at',
        'programming_id',
        'paymentConcept_id',
        'box_id',
        'bank_id',
        'branchOffice_id',
        'reception_id',
        'person_id',
        'person_reception_id',
        'user_id',
        'mov_id',
        'driverExpense_id',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    public function getFormaPagoAttribute()
    {
        $resultado = DB::select(DB::raw('SELECT obtenerFormaPagoPorCaja(:id) AS formaPago'), ['id' => 1]);
        $formapago = $resultado[0]->formaPago;

        return $formapago;
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
    public function movVenta()
    {
        return $this->belongsTo(Moviment::class, 'mov_id');
    }
    public function paymentConcept()
    {
        return $this->belongsTo(PaymentConcept::class, 'paymentConcept_id');
    }
    //NOSE USA
    public function detailsMoviment()
    {
        return $this->hasMany(DetailMoviment::class, 'moviment_id');
    }
    //SI SE USA
    public function detalles()
    {
        return $this->hasMany(DetalleMoviment::class);
    }
    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function reception()
    {
        return $this->belongsTo(Reception::class, 'reception_id');
    }
    public function receptions()
    {
        return $this->hasMany(Reception::class)
            ->select('id', 'codeReception', 'conditionPay', 'paymentAmount', 'moviment_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function personreception()
    {
        return $this->belongsTo(Person::class, 'person_reception_id');
    }
    public function driverExpense()
    {
        return $this->belongsTo(DriverExpense::class, 'driverExpense_id');
    }
    public function payinstallment()
    {
        return $this->belongsTo(PayInstallment::class, 'pay_installment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function installmentPendientes()
    {
        return $this->hasMany(Installment::class)
            ->where('totalDebt', '>',0)
            ->orderBy('date', 'asc'); // Prioriza las fechas de vencimiento más próximas
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class);
    }

    public function updateSaldo()
    {
        // Sumar el totalDebt de todas las installments relacionadas
        $totalDebt = $this->installments()->sum('totalDebt');

        // Actualizar el campo saldo
        $this->saldo = $totalDebt;
        if ($this->saldo == 0) {

            $this->status = 'Pagado';

        }
        if ($this->status_facturado == "Anulada") {
            $this->status = 'Anulada';
        }
        if ($this->status == 'Pagado' && $this->saldo != 0) {
            $this->status = 'Pendiente';
        }

        $this->save();
    }
    public function storeTotalInCredit()
    {
        // Sumar el totalDebt de todas las installments relacionadas
        $totalDebt   = $this->installments()->sum('totalDebt');
        $this->total = $totalDebt;
        $this->saldo = $totalDebt;
        $this->save();

    }

}
