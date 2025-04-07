<?php
namespace App\Http\Controllers\AccountPayable;

use App\Http\Controllers\Controller;

use App\Http\Requests\TypeDocumentRequest\IndexTypeDocumentRequest;
use App\Http\Resources\TypeDocumentResource;
use App\Models\Type_document;

class TypeDocumentController extends Controller
{
    public function index(IndexTypeDocumentRequest $request)
    {

        return $this->getFilteredResults(
            Type_document::class,
            $request,
            Type_document::filters,
            Type_document::sorts,
            TypeDocumentResource::class
        );
    }
}
