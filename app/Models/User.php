<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *     schema="Option_Menu",
 *     title="option_menu",
 *     description="Modelo de Opcion de Menu",
 *     required={"name", "state"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del Opcion de Menu"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del Opcion de Menu"
 *     ),
 *      @OA\Property(
 *         property="route",
 *         type="string",
 *         description="Route del Opcion de Menu"
 *     ),
 *      @OA\Property(
 *         property="icon",
 *         type="string",
 *         description="Icon del Opcion de Menu"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="boolean",
 *         description="Estado del Opcion de Menu"
 *     ),

 *     @OA\Property(
 *         property="guard_name",
 *         type="string",
 *         description="Nombre de la guardia asociada al Opcion de Menu"
 *     ),
 *   @OA\Property(
 *         property="groupMenu_id",
 *         type="integer",
 *         description="User groupMenu"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de creación del Opcion de Menu"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de actualización del Opcion de Menu"
 *     ),
 *   @OA\Property(
 *         property="pivot",
 *         type="object",
 *         description="Información de la tabla pivot",
 *         @OA\Property(
 *             property="role_id",
 *             type="integer",
 *             description="ID del rol asociado"
 *         ),
 *         @OA\Property(
 *             property="permission_id",
 *             type="integer",
 *             description="ID del permiso asociado"
 *         )
 *     ),
 *  @OA\Property(
 *         property="groupMenu",
 *         ref="#/components/schemas/GroupMenu",
 *         description="GroupMenu asociada al trabajador"
 *     ),
 * ),

 * @OA\Schema(
 *     schema="TypeOf_User",
 *     title="typeof_user",
 *     description="Modelo de Tipo de Usuario",
 *     required={"name", "state"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del tipo de usuario"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del tipo de usuario"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="boolean",
 *         description="Estado del tipo de usuario"
 *     ),
 *     @OA\Property(
 *         property="guard_name",
 *         type="string",
 *         description="Nombre de la guardia asociada al tipo de usuario"
 *     ),
 *   @OA\Property(
 *         property="groupMenu_id",
 *         type="integer",
 *         description="User groupMenu"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de creación del tipo de usuario"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de actualización del tipo de usuario"
 *     ),

 * )
 */

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="User",
     *     title="user",
     *     description="User model",
     * required={"id", "username", "typeofUser_id", "worker_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int64",
     *         description="User ID"
     *     ),
     *     @OA\Property(
     *         property="username",
     *         type="string",
     *         description="Username"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="string",
     *         description="User state"
     *     ),
     * @OA\Property(
     *         property="typeofUser_id",
     *         type="integer",
     *         description="User typeofUser"
     *     ),
     * @OA\Property(
     *         property="worker_id",
     *         type="integer",
     *         description="User worker"
     *     ),
     *        @OA\Property(
     *         property="box_id",
     *         type="integer",
     *         description="Id de la caja"
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
     *         description="Last update date"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Deletion date"
     *     ),
     *      *     @OA\Property(
     *         property="box",
     *         ref="#/components/schemas/Box",
     *         description="Caja asignada al trabajador"
     *     ),
     *     @OA\Property(
     *         property="worker",
     *         ref="#/components/schemas/Worker",
     *         description="Sucursal asociada al trabajador"
     *     ),
     *      @OA\Property(
     *         property="typeOf_User",
     *         ref="#/components/schemas/TypeOf_User",
     *         description="Tipo de usuario del trabajador"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'username',
        'password',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
        'box_id',
        'worker_id',
        'typeofUser_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
    public function typeofUser()
    {
        return $this->belongsTo(Role::class, 'typeofUser_id');
    }
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }
}
