<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    /**
     * @OA\Schema(
     *     schema="Worker",
     *     title="worker",
     *     description="Modelo de Trabajador",
     *     required={"id","department", "province", "district", "occupation", "position", "center", "typeRelationship", "startDate", "person_id", "area_id", "branchOffice_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del trabajador"
     *     ),
     *     @OA\Property(
     *         property="code",
     *         type="string",
     *         description="Código del trabajador"
     *     ),
     *     @OA\Property(
     *         property="department",
     *         type="string",
     *         description="Departamento del trabajador"
     *     ),
     *     @OA\Property(
     *         property="province",
     *         type="string",
     *         description="Provincia del trabajador"
     *     ),
     *     @OA\Property(
     *         property="district",
     *         type="string",
     *         description="Distrito del trabajador"
     *     ),
     *     @OA\Property(
     *         property="maritalStatus",
     *         type="string",
     *         description="Estado civil del trabajador"
     *     ),
     *     @OA\Property(
     *         property="levelInstitution",
     *         type="string",
     *         description="Nivel de institución del trabajador"
     *     ),
     *     @OA\Property(
     *         property="occupation",
     *         type="string",
     *         description="Ocupación del trabajador"
     *     ),
     *     @OA\Property(
     *         property="satus",
     *         type="string",
     *         description="Estado del trabajador"
     *     ),
     *     @OA\Property(
     *         property="startDate",
     *         type="string",
     *         format="date",
     *         description="Fecha de inicio del trabajador"
     *     ),
     *     @OA\Property(
     *         property="endDate",
     *         type="string",
     *         format="date",
     *         description="Fecha de fin del trabajador"
     *     ),
     *      @OA\Property(
     *         property="person_id",
     *         type="integer",
     *         description="User person"
     *     ),
     *      @OA\Property(
     *         property="area_id",
     *         type="integer",
     *         description="User area"
     *     ),
     *      @OA\Property(
     *         property="branchOffice_id",
     *         type="integer",
     *         description="User branchOffice"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado del trabajador"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del trabajador"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del trabajador"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de eliminación del trabajador"
     *     ),
     *     @OA\Property(
     *         property="person",
     *          ref="#/components/schemas/Person",
     *         description="ID de la persona asociada al trabajador"
     *     ),
     *      @OA\Property(
     *         property="area",
     *         ref="#/components/schemas/Area",
     *         description="Area asociada al trabajador"
     *     ),
     *     @OA\Property(
     *         property="branchOffice",
     *         ref="#/components/schemas/BranchOffice",
     *         description="Sucursal asociada al trabajador"
     *     )
     * )
     */

    use SoftDeletes;
    protected $fillable = [
        'code',
        'department',
        'province',
        'district',

        'maritalStatus',
        'levelInstitution',
        'occupation',
        'licencia',
        'licencia_date',
        'pathPhoto',

        'status',
        'startDate',
        'endDate',
        'state',

        'district_id',
        'person_id',
        'area_id',
        'branchOffice_id',

    ];

    const fields_export = [
        'Licencia' => 'licencia',
        'Fecha Licencia' => 'licencia_date',
        'Nro Identidad' => 'person.documentNumber',
        'Nombre' => 'person.names',
        'Apellido Paterno' => 'person.fatherSurname',
        'Telefono' => 'person.telephone',
        'Ocuapación' => 'occupation',
    ];
    const filters = [
        'bank_id'          => '=',
        'code'             => 'like',
        'department'       => 'like',
        'province'         => 'like',
        'district'         => 'like',
        'person.deleted_at'         => '=',

        'maritalStatus'    => 'like',
        'levelInstitution' => 'like',
        'occupation'       => 'like',
        'person.names'       => 'like',
        'licencia'         => 'like',
        'licencia_date'    => 'date',

        'status'           => '=',
        'startDate'        => 'date',
        'endDate'          => '=',
        'state'            => '=',

        'district_id'      => '=',
        'person_id'        => '=',
        'area_id'          => '=',
        'branchOffice_id'  => '=',
    ];
    const sorts = [
        'id' => 'desc',
    ];
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

    public function detailWorkers()
    {
        return $this->hasMany(DetailWorker::class, 'worker_id');
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    // En el modelo Worker
    public function carrierGuidesPilotos()
    {
        return $this->hasMany(CarrierGuide::class, 'driver_id');
    }
    public function carrierGuidesCoPilotos()
    {
        return $this->hasMany(CarrierGuide::class, 'copilot_id');
    }

}
