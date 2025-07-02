<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'code',           // Código único del neumático
        'condition',      // Condición: Nuevo, Usado, Reencauchado
        'retread_number', // Número de reencauche
        'entry_date',     // Fecha de ingreso
        'supplier_id',    // ID del proveedor
        'vehicle_id',    // ID del neumatico
        'material',       // Material del neumático
        'brand',          // Marca del neumático
        'design',         // Diseño del neumático
        'type',           // Tipo de neumático
        'size',           // Medida del neumático
        'dot',            // Fecha de fabricación (DOT)
        'tread_type',     // Tipo de banda
        'current_tread',  // Cocada actual
        'minimum_tread',  // Cocada mínima permitida
        'tread',          // Cocada inicial o general
        'shoulder1',      // Ribete 1
        'shoulder2',      // Ribete 2
        'shoulder3',      // Ribete 3
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const filters = [
        'id' => '=',
        'code' => 'like',
        'condition' => '=',
        'retread_number' => '=',
        'entry_date' => 'date',
        'supplier_id' => '=',
        'vehicle_id' => '=',
        'material' => 'like',
        'brand' => 'like',
        'design' => 'like',
        'type' => 'like',
        'size' => 'like',
        'dot' => 'like',
        'tread_type' => 'like',
        'current_tread' => '=',
        'minimum_tread' => '=',
        'tread' => '=',
        'shoulder1' => '=',
        'shoulder2' => '=',
        'shoulder3' => '=',
        'created_at' => 'date',
        'updated_at' => 'date',
        'deleted_at' => 'date',
    ];


    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
        'entry_date' => 'desc',
        'code' => 'asc',
    ];

    /**
     * Relación con el proveedor (persona)
     */
    public function supplier()
    {
        return $this->belongsTo(Person::class, 'supplier_id');
    }
}
