<?php
namespace App\Http\Requests\TarifarioRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="TarifarioRequest",
 *     title="TarifarioRequest",
 *     description="Request model for Tarifario information with filters and sorting",
 *     required={"id", "quantity", "person_id"},
 *     @OA\Property(property="id", type="integer", nullable=true, description="ID of the Tarifario"),
 *     @OA\Property(property="tarifa", type="string", nullable=true, description="Tarifa description"),
 *     @OA\Property(property="quantity", type="string", nullable=true, description="Quantity of the item"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Description of the Tarifario"),
 *     @OA\Property(property="unity_id", type="string", nullable=true, description="ID of the unity associated with the Tarifario"),
 *     @OA\Property(property="person_id", type="string", nullable=true, description="ID of the person associated with the Tarifario")
 * )
 */

class StoreTarifarioRequest extends StoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */ 

 
     public function rules()
     {
         return [
             'tarifa'       => 'nullable|numeric',
             'description'  => 'nullable|string',
             'person_id'    => 'nullable|string',
             'unity_id'     => [
                 'required',
                 'exists:unities,id,deleted_at,NULL', // Verifica que la unidad exista y no esté eliminada
                 Rule::unique('tarifarios')
                     ->where('person_id', $this->person_id)
                     ->where('unity_id', $this->unity_id)
                     ->whereNull('deleted_at') // Verifica que no haya tarifas activas para esa persona y unidad
                      // Ignora el registro actual si es una actualización
             ],
         ];
     }
     
     public function messages()
     {
         return [
             'tarifa.numeric'       => 'La tarifa debe ser decimal.',
             'description.string'  => 'La descripción debe ser un texto válido.',
             'person_id.string'    => 'El ID de la persona debe ser un texto válido.',
             'unity_id.required'   => 'La unidad es obligatoria.',
             'unity_id.exists'     => 'La unidad seleccionada no es válida o no está activa.',
             'unity_id.unique'     => 'La persona ya tiene una tarifa asociada a esta unidad.',
         ];
     }
     
    

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
