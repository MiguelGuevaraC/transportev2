<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="Person",
     *     title="person",
     *     description="Modelo de Persona",
     *     required={"id","typeofDocument", "documentNumber","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID de la persona"
     *     ),
     *     @OA\Property(
     *         property="typeofDocument",
     *         type="string",
     *         description="Tipo de documento de la persona"
     *     ),
     *     @OA\Property(
     *         property="documentNumber",
     *         type="string",
     *         description="Número de documento de la persona"
     *     ),
     *     @OA\Property(
     *         property="names",
     *         type="string",
     *         description="Nombres de la persona"
     *     ),
     *     @OA\Property(
     *         property="fatherSurname",
     *         type="string",
     *         description="Apellido paterno de la persona"
     *     ),
     *     @OA\Property(
     *         property="motherSurname",
     *         type="string",
     *         description="Apellido materno de la persona"
     *     ),
     *     @OA\Property(
     *         property="birthDate",
     *         type="string",
     *         format="date",
     *         description="Fecha de nacimiento de la persona"
     *     ),
     *     @OA\Property(
     *         property="address",
     *         type="string",
     *         description="Dirección de la persona"
     *     ),
     *     @OA\Property(
     *         property="telephone",
     *         type="string",
     *         description="Teléfono de la persona"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         description="Correo electrónico de la persona"
     *     ),
     *     @OA\Property(
     *         property="businessName",
     *         type="string",
     *         description="Nombre de la empresa de la persona"
     *     ),
     *     @OA\Property(
     *         property="comercialName",
     *         type="string",
     *         description="Nombre comercial de la persona"
     *     ),
     *     @OA\Property(
     *         property="fiscalAddress",
     *         type="string",
     *         description="Dirección fiscal de la persona"
     *     ),
     *     @OA\Property(
     *         property="places",
     *         type="string",
     *         description="Lugares del proveedor"
     *     ),
     *     @OA\Property(
     *         property="representativePersonDni",
     *         type="string",
     *         description="DNI del representante de la persona"
     *     ),
     *     @OA\Property(
     *         property="representativePersonName",
     *         type="string",
     *         description="Nombre del representante de la persona"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado de la persona"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación de la persona"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización de la persona"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de eliminación de la persona"
     *     ),
     *              @OA\Property(
     *         property="branch_office",
     *          ref="#/components/schemas/BranchOffice",
     *         description="BranchOffice"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'typeofDocument',
        'documentNumber',
        'names',
        'fatherSurname',
        'motherSurname',
        'birthDate',
        'address',
        'places',
        'telephone',
        'email',
        'daysCredit',
        'type',

        'businessName',
        'comercialName',
        'fiscalAddress',
        'representativePersonDni',
        'representativePersonName',
        'branchOffice_id',

        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function receptions()
    {
        return $this->hasMany(Reception::class, 'payResponsible_id');
    }
    public function worker()
    {
        return $this->hasOne(Worker::class, 'person_id');
    }

    public function contacts()
    {
        return $this->hasMany(ContactInfo::class);
    }

    // Obtener las recepciones que tienen un debtAmount mayor a 0
    public function receptionsWithDebt()
    {
        // return $this->receptions()->where('debtAmount', '>', 0);
        return $this->receptions();
    }

    public function movimentsVenta()
    {
        return $this->hasMany(Moviment::class, 'person_id')->where('movType', 'Venta');
    }

}
