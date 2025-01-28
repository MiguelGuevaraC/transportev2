<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bitacora extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', // ID del usuario que realizó la acción
        'record_id', // ID del registro afectado en la tabla mencionada.
        'action', // Cambios realizados (opcional, puede ser un JSON)
        'table_name', //ombre de la tabla afectada
        'data', // objeto en json
        'description', //Descripción detallada de la actividad
        'ip_address', //Dirección IP del usuario
        'user_agent', //nformación sobre el navegador o dispositivo utilizado.
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
