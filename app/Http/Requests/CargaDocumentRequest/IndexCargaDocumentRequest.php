<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\IndexRequest;
use App\Models\Product;

class IndexCargaDocumentRequest extends IndexRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

            'code_doc'        => 'nullable|string',
            'person_id'       => 'nullable|string',
            'product_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (! is_numeric($value) && ! is_array($value) && $value !== "null") {
                        return $fail('No se está enviando de forma correcta los productos.');
                    }

                    if ($value !== "null") {
                        $ids = is_array($value) ? $value : [$value];
                        if (Product::whereIn('id', $ids)->whereNull('deleted_at')->count() !== count($ids)) {
                            $fail('Uno o más productos no existen o han sido eliminados.');
                        }
                    }
                },
            ],
            'branchOffice_id' => 'nullable|string',
            'quantity'        => 'nullable|string',
            'movement_type'   => 'nullable|string',
            'num_anexo'       => 'nullable|string',
            'comment'         => 'nullable|string',
            'description'     => 'nullable|string',
            'movement_date'   => 'nullable|string',
            'weight'          => 'nullable|string',
            'lote_doc'        => 'nullable|string',
            'date_expiration' => 'nullable|string',

        ];
    }

}
