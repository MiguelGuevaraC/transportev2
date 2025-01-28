<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMenu extends Model
{

    /**
     * @OA\Schema(
     *     schema="GroupMenu",
     *     title="group_menu",
     *     description="Modelo de Menú de Grupo",
     *     required={"id","name", "icon","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del menú de grupo"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nombre del menú de grupo"
     *     ),
     *     @OA\Property(
     *         property="icon",
     *         type="string",
     *         description="Ícono del menú de grupo"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado del menú de grupo"
     *     ), @OA\Property(
     *         property="groupMenu",
     *         type="integer",
     *         description="Grupo padre"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del menú de grupo"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del menú de grupo"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'name',
        'icon',
        'state',
        'groupMenu_id',
        'created_at',
        'updated_at',

    ];
}
