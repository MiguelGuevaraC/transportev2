<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programming extends Model
{
    use SoftDeletes;
/**
 * @OA\Schema(
 *     schema="Programming",
 *     title="Programming",
 *     description="Programming schema",
 *     required={
 *         "id", "departureDate", "estimatedArrivalDate", "state",
 *         "origin_id", "destination_id", "tract_id", "platForm_id",
 *         "created_at", "updated_at", "deleted_at"
 *     },
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Programming ID"
 *     ),
 *     @OA\Property(
 *         property="numero",
 *         type="string",
 *         description="Number Tip"
 *     ),
 *     @OA\Property(
 *         property="departureDate",
 *         type="string",
 *         format="date-time",
 *         description="Departure date"
 *     ),
 *     @OA\Property(
 *         property="estimatedArrivalDate",
 *         type="string",
 *         format="date-time",
 *         description="Estimated arrival date"
 *     ),
 *     @OA\Property(
 *         property="actualArrivalDate",
 *         type="string",
 *         format="date-time",
 *         description="Actual arrival date"
 *     ),
 *     @OA\Property(
 *         property="isload",
 *         type="boolean",
 *         description="Indicates if the programming is loaded"
 *     ),
 *     @OA\Property(
 *         property="totalWeight",
 *         type="number",
 *         format="float",
 *         description="Total weight in the programming"
 *     ),
 *     @OA\Property(
 *         property="totalViaje",
 *         type="number",
 *         format="float",
 *         description="Total trip cost"
 *     ),
 *     @OA\Property(
 *         property="carrierQuantity",
 *         type="integer",
 *         description="Quantity of carriers involved"
 *     ),
 *     @OA\Property(
 *         property="detailQuantity",
 *         type="integer",
 *         description="Quantity of details"
 *     ),
 *     @OA\Property(
 *         property="totalAmount",
 *         type="number",
 *         format="float",
 *         description="Total amount of the programming"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         description="State of the programming"
 *     ),
 *     @OA\Property(
 *         property="dateLiquidacion",
 *         type="string",
 *         format="date",
 *         description="Date of liquidation"
 *     ),
 *     @OA\Property(
 *         property="statusLiquidacion",
 *         type="integer",
 *         description="Status of the liquidation"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Current status of the programming"
 *     ),
 *     @OA\Property(
 *         property="origin_id",
 *         type="integer",
 *         description="ID of the origin"
 *     ),
 *     @OA\Property(
 *         property="destination_id",
 *         type="integer",
 *         description="ID of the destination"
 *     ),
 *     @OA\Property(
 *         property="tract_id",
 *         type="integer",
 *         description="ID of the tract"
 *     ),
 *     @OA\Property(
 *         property="platForm_id",
 *         type="integer",
 *         description="ID of the platform"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation date"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Update date"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Deletion date"
 *     ),
 *     @OA\Property(
 *         property="origin",
 *         ref="#/components/schemas/Place",
 *         description="Origin place"
 *     ),
 *     @OA\Property(
 *         property="destination",
 *         ref="#/components/schemas/Place",
 *         description="Destination place"
 *     ),
 *     @OA\Property(
 *         property="tract",
 *         ref="#/components/schemas/Vehicle",
 *         description="Tract vehicle"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         ref="#/components/schemas/Vehicle",
 *         description="Platform vehicle"
 *     ),
 *     @OA\Property(
 *         property="branch_office",
 *         ref="#/components/schemas/BranchOffice",
 *         description="Branch office associated"
 *     )
 * )
 */

    protected $fillable = [
        'id',
        'numero',
        'departureDate',
        'estimatedArrivalDate',
        'actualArrivalDate',

        'state',
        'isload',

        'totalWeight',
        'carrierQuantity',
        'detailQuantity',
        'totalAmount',

        'kmStart',
        'kmEnd',
        'totalViaje',
        'totalExpenses',
        'totalReturned',

        'statusLiquidacion',
        'dateLiquidacion',

        'origin_id',
        'destination_id',
        'tract_id',
        'platForm_id',
        'branchOffice_id',
        'programming_id',
        'status',

        'user_edited_id',
        'user_deleted_id',
        'user_created_id',

        'created_at',
        'updated_at',
        'deleted_at',

    ];
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

    public function tract()
    {
        return $this->belongsTo(Vehicle::class, 'tract_id');
    }
    public function platForm()
    {
        return $this->belongsTo(Vehicle::class, 'platForm_id');
    }

    public function origin()
    {
        return $this->belongsTo(Place::class, 'origin_id');
    }

    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function reprogramming()
    {
        return $this->hasOne(Programming::class, 'programming_id');
    }

    public function destination()
    {
        return $this->belongsTo(Place::class, 'destination_id');
    }
    public function detailsWorkers()
    {
        return $this->hasMany(DetailWorker::class);
    }

    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'detail_workers');
    }

    public function driver()
    {
        return $this->detailsWorkers()->where('function', 'driver')->first();
    }

    public function detailReceptions()
    {
        return $this->hasMany(DetailReception::class);
    }
    public function detailReceptionsWithGrt()
    {
        $hasCarrierGuide = $this->detailReceptions()->whereHas('reception.firstCarrierGuide')->count();
        return $hasCarrierGuide;
    }
    public function carrierGuides()
    {
        return $this->hasMany(CarrierGuide::class);
    }

    public function driverExpenses()
    {
        return $this->hasMany(DriverExpense::class);
    }

    public function updateTotalDriversExpenses()
    {
        $programming = $this;

        // Calcular total de gastos (excepto el concepto con ID 1)
        // Calcular total de gastos excluyendo el concepto de viaje (ID != 1)
        $programming->totalExpenses = $programming->driverExpenses()
            ->where('expensesConcept_id', '!=', 1)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                // $query->where('selectTypePay', 'Efectivo')
                //     ->orWhere('selectTypePay', 'Descuento_sueldo')
                //     ->orWhere('selectTypePay', 'Proxima_liquidacion');
            })
            ->sum('total');

// Calcular total de gastos para el concepto de viaje (ID = 1)
        $programming->totalViaje = $programming->driverExpenses()
            ->where('expensesConcept_id', '=', 1)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                // $query->where('selectTypePay', 'Efectivo')
                //     ->orWhere('selectTypePay', 'Descuento_sueldo')
                //     ->orWhere('selectTypePay', 'Proxima_liquidacion');
            })
            ->sum('total');

        // Calcular total devuelto
        $programming->totalReturned = $programming->totalViaje - $programming->totalExpenses;

        // Guardar cambios
        $programming->save();
    }
    public function tractVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'tract_id');
    }

    // Relación inversa con el vehículo a través de platForm_id
    public function platformVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'platForm_id');
    }
    public function carrierGuidess()
    {
        return $this->belongsToMany(CarrierGuide::class, 'carrier_by_programmings', 'programming_id', 'carrier_guide_id');
    }

}
