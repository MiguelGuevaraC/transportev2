<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\IndexRequest;

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
            'product_id'      => 'nullable|string',
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
