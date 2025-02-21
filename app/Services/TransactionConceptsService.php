<?php
namespace App\Services;

use App\Models\TransactionConcept;

class TransactionConceptsService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTransactionConceptById(int $id): ?TransactionConcept
    {
        return TransactionConcept::find($id);
    }

    public function createTransactionConcept(array $data): TransactionConcept
    {
        $proyect = TransactionConcept::create($data);
        return $proyect;
    }

    public function updateTransactionConcept(TransactionConcept $proyect, array $data): TransactionConcept
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return TransactionConcept::find($id)?->delete() ?? false;
    }

}
