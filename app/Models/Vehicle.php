<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="Vehicle",
     *     title="vehicle",
     *     description="Vehicle model",
     *     required={"status","oldPlate","currentPlate","numberMtc","brand","numberModel","tara","netWeight","usefulLoad","ownerCompany","length","width","height","ejes","wheels","color","year","tireType","tireSuspension","created_at","updated_at","deleted_at","modelVehicle_id","typeVehicle_id"},
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Status of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="oldPlate",
     *         type="string",
     *         description="Old plate of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="currentPlate",
     *         type="string",
     *         description="Current plate of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="numberMtc",
     *         type="string",
     *         description="Number MTC of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="brand",
     *         type="string",
     *         description="Brand of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="numberModel",
     *         type="string",
     *         description="Number model of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="tara",
     *         type="number",
     *         format="float",
     *         description="Tara of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="netWeight",
     *         type="number",
     *         format="float",
     *         description="Net weight of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="usefulLoad",
     *         type="number",
     *         format="float",
     *         description="Useful load of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="ownerCompany",
     *         type="string",
     *         description="Owner company of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="length",
     *         type="number",
     *         format="float",
     *         description="Length of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="width",
     *         type="number",
     *         format="float",
     *         description="Width of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="height",
     *         type="number",
     *         format="float",
     *         description="Height of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="ejes",
     *         type="integer",
     *         description="Number of axles of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="wheels",
     *         type="integer",
     *         description="Number of wheels of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="color",
     *         type="string",
     *         description="Color of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="year",
     *         type="integer",
     *         description="Year of manufacture of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="tireType",
     *         type="string",
     *         description="Type of tires of the vehicle"
     *     ),
     *     @OA\Property(
     *         property="tireSuspension",
     *         type="string",
     *         description="Type of tire suspension of the vehicle"
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
     *         property="modelVehicle_id",
     *         type="integer",
     *         description="ID of the vehicle model"
     *     ),
     *     @OA\Property(
     *         property="typeVehicle_id",
     *         type="integer",
     *         description="ID of the vehicle type"
     *     ),
     *           @OA\Property(
     *         property="modeloFuncional",
     *         ref="#/components/schemas/Model_functional",
     *         description="Model_functional asociada al trabajador"
     *     ),
    
     *              @OA\Property(
     *         property="branch_office",
     *          ref="#/components/schemas/BranchOffice",
     *         description="BranchOffice"
     *     ),
     * )
     */

    protected $fillable = [
        'status',
        'oldPlate',
        'serie',
        'currentPlate',
        'numberMtc',
        'brand',
        'numberModel',
        'typeCar',
        'tara',
        'netWeight',
        'usefulLoad',
        'ownerCompany',
        'length',
        'width',
        'height',
        'ejes',
        'wheels',
        'color',
        'year',
        'tireType',
        'tireSuspension',

        'bonus',
        'isConection',
        'companyGps_id',
        'mode',
        'responsable_id',

   

        'created_at',
        'updated_at',
        'deleted_at',
        // 'fleet_id',
        'typeCarroceria_id',
        'modelVehicle_id',
        'branchOffice_id',
        'typeVehicle_id',
    ];



    public function modelFunctional()
    {
        return $this->belongsTo(ModelFunctional::class, 'modelVehicle_id');
    }

    // public function fleet()
    // {
    //     return $this->belongsTo(Fleet::class, 'fleet_id');
    // }

    public function photos()
    {
        return $this->hasMany(Photos::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class)->where('state', 1);
    }
    

    public function tractProgrammings()
    {
        return $this->hasMany(Programming::class, 'tract_id');
    }

    // Relación con el modelo Programming cuando el vehículo está en platForm_id
    public function platformProgrammings()
    {
        return $this->hasMany(Programming::class, 'platForm_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
    public function companyGps()
    {
        return $this->belongsTo(Person::class, 'companyGps_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Person::class, 'responsable_id');
    }
    public function typeCarroceria()
    {
        return $this->belongsTo(TypeCarroceria::class,'typeCarroceria_id');
    }

}
