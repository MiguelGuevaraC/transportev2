<?php
namespace App\Http\Requests\DriverExpenseRequest;

use App\Http\Requests\StoreRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="TransferSaldoRequest",
 *     title="TransferSaldoRequest",
 *     description="Request model for Concept Transaction with validation rules",
 *     required={"driver_id", "programming_out_id", "programming_in_id", "amount"},
 *     @OA\Property(property="driver_id", type="integer", example=5, description="ID del conductor"),
 *     @OA\Property(property="programming_out_id", type="integer", example=10, description="ID de la programación de salida"),
 *     @OA\Property(property="programming_in_id", type="integer", example=20, description="ID de la programación de destino"),
 *     @OA\Property(property="amount", type="number", format="float", example=150.00, description="Monto a transferir"),
 * )
 */
class TransferSaldoRequest extends StoreRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'driver_id'          => [
                'required',
                'integer',
                Rule::exists('workers', 'id')->whereNull('deleted_at'),
            ],
            'programming_out_id' => [
                'required',
                'integer',
                Rule::exists('programmings', 'id')->whereNull('deleted_at'),
            ],
            'programming_in_id'  => [
                'required',
                'integer',
                Rule::exists('programmings', 'id')->whereNull('deleted_at'),
                'different:programming_out_id',
            ],
            'amount'             => 'required|numeric|min:1',
        ];
    }

    /**
     * Mensajes de error personalizados.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'driver_id.required'          => 'El ID del conductor es obligatorio.',
            'driver_id.exists'            => 'El conductor seleccionado no existe o ha sido eliminado.',
            'programming_out_id.required' => 'La programación de salida es obligatoria.',
            'programming_out_id.exists'   => 'La programación de salida no existe o ha sido eliminada.',
            'programming_in_id.required'  => 'La programación de destino es obligatoria.',
            'programming_in_id.exists'    => 'La programación de destino no existe o ha sido eliminada.',
            'programming_in_id.different' => 'La programación de destino debe ser diferente a la de salida.',
            'amount.required'             => 'El monto es obligatorio.',
            'amount.numeric'              => 'El monto debe ser un número válido.',
            'amount.min'                  => 'El monto debe ser mayor a 0.',
        ];
    }

    /**
     * Personaliza los atributos para los errores de validación.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'driver_id'          => 'conductor',
            'programming_out_id' => 'programación de salida',
            'programming_in_id'  => 'programación de destino',
            'amount'             => 'monto',
        ];
    }
}
