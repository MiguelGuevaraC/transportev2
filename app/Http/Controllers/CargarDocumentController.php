<?php
namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Exports\KardexExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CargaDocumentRequest\IndexCargaDocumentRequest;
use App\Http\Requests\CargaDocumentRequest\KardexRequest;
use App\Http\Requests\CargaDocumentRequest\StoreCargaDocumentRequest;
use App\Http\Requests\CargaDocumentRequest\UpdateCargaDocumentRequest;
use App\Http\Resources\CargaDetailResource;
use App\Http\Resources\CargaResource;
use App\Models\CargaDocument;
use App\Models\DocumentCargaDetail;
use App\Models\Product;
use App\Services\CargaDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CargarDocumentController extends Controller
{
    protected $cargaDocumentService;

    public function __construct(CargaDocumentService $CargaDocumentService)
    {
        $this->cargaDocumentService = $CargaDocumentService;
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/cargaDocument",
 *     summary="Obtener información de CargaDocuments con filtros y ordenamiento",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},

 *     @OA\Parameter(name="code_doc", in="query", description="Filtrar por código del documento", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="person_id", in="query", description="Filtrar por ID de la persona responsable", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="product_id", in="query", description="Filtrar por ID del producto relacionado", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="branchOffice_id", in="query", description="Filtrar por ID de la sucursal", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="quantity", in="query", description="Filtrar por cantidad de producto movido", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="movement_type", in="query", description="Filtrar por tipo de movimiento (ENTRADA, SALIDA)", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="num_anexo", in="query", description="Filtrar por número de anexo", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="comment", in="query", description="Filtrar por comentario adicional", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="description", in="query", description="Filtrar por descripción", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="movement_date", in="query", description="Filtrar por fecha de movimiento", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="weight", in="query", description="Filtrar por peso del producto", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="lote_doc", in="query", description="Filtrar por lote del documento", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="date_expiration", in="query", description="Filtrar por fecha de vencimiento", required=false, @OA\Schema(type="string", format="date")),

 *     @OA\Response(response=200, description="Lista de CargaDocuments",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/CargaDocument"))
 *     ),
 *     @OA\Response(response=422, description="Validación fallida",
 *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
 *     )
 * )
 */
    public function index(IndexCargaDocumentRequest $request)
    {

        return $this->getFilteredResults(
            CargaDocument::class,
            $request,
            CargaDocument::filters,
            CargaDocument::sorts,
            CargaResource::class
        );
    }

    public function index_history(IndexCargaDocumentRequest $request)
    {
        $query = DocumentCargaDetail::query();
    
        // Filtro por rango de fechas en la relación "document_carga"
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereHas('document_carga', function ($q) use ($request) {
                $q->whereBetween('movement_date', [
                    $request->input('from'),
                    $request->input('to')
                ]);
            });
        }
    
        return $this->getFilteredResults(
            $query,
            $request,
            DocumentCargaDetail::filters,
            DocumentCargaDetail::sorts,
            CargaDetailResource::class
        );
    }

    public function index_export_excel(IndexCargaDocumentRequest $request)
    {
        $request['all'] = "true";
        $data           = $this->index_history($request);
        $fileName       = 'Caja_Grande_' . now()->timestamp . '.xlsx';
        $columns        = DocumentCargaDetail::fields_export;
        return Excel::download(new ExcelExport($data, $columns, 0), $fileName);
    }
    
    
/**
 * @OA\Get(
 *     path="/transportedev/public/api/cargaDocument/{id}",
 *     summary="Obtener detalles de un Documento de Carga por ID",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del CargaDocument", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Documento de Carga encontrado", @OA\JsonContent(ref="#/components/schemas/CargaDocument")),
 *     @OA\Response(response=404, description="Documento de Carga no encontrado", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Documento de Carga no encontrado")))
 * )
 */

    public function show($id)
    {

        $carga = $this->cargaDocumentService->getCargaDocumentById($id);

        if (! $carga) {
            return response()->json([
                'error' => 'Documento de Carga No Encontrado',
            ], 404);
        }

        return new CargaResource($carga);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/cargaDocument",
 *     summary="Crear CargaDocument",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CargaDocumentRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Documento de Carga creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CargaDocument")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error de validación")
 *         )
 *     )
 * )
 */

 public function store(StoreCargaDocumentRequest $request)
 {
     $data = $request->validated();
     $data['user_created_id'] = auth()->id(); // o Auth::id()
     $carga = $this->cargaDocumentService->createCargaDocument($data);
     return new CargaResource($carga);
 }
 

/**
 * @OA\Put(
 *     path="/transportedev/public/api/cargaDocument/{id}",
 *     summary="Actualizar un CargaDocument",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CargaDocumentRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Documento de Carga actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CargaDocument")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error de validación")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Documento de Carga no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Documento de Carga no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error interno del servidor")
 *         )
 *     )
 * )
 */

    public function update(UpdateCargaDocumentRequest $request, $id)
    {

        $validatedData = $request->validated();

        $carga = $this->cargaDocumentService->getCargaDocumentById($id);
        if (! $carga) {
            return response()->json([
                'error' => 'Documento de Carga No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->cargaDocumentService->updateCargaDocument($carga, $validatedData);
        return new CargaResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/cargadocument/{id}",
 *     summary="Eliminar un Documento de Cargapor ID",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Documento de Carga eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Documento de Carga eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Documento de Carga no encontrado"))),

 * )
 */

    public function destroy($id)
    {

        $proyect = $this->cargaDocumentService->getCargaDocumentById($id);

        if (! $proyect) {
            return response()->json([
                'error' => 'Documento de Carga No Encontrado.',
            ], 404);
        }
        $proyect = $this->cargaDocumentService->destroyById($id);

        return response()->json([
            'message' => 'Documento de Carga eliminado exitosamente',
        ], 200);
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/export-kardex",
 *     summary="Exportar Kardex",
 *     tags={"CargaDocument"},
  *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Archivo exportado correctamente",
 *         @OA\Header(
 *             header="Content-Disposition",
 *             @OA\Schema(type="string", example="attachment; filename=kardex.xlsx")
 *         ),
 *         @OA\MediaType(
 *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
 *         )
 *     )
 * )
 */
    public function exportKardex(KardexRequest $request)
    {
        $validatedData = $request->validated();
        $idproducto = isset($request->product_id) && $request->product_id !== "null"
        ? (is_array($request->product_id) ? $request->product_id : [$request->product_id])
        : null;
    

        $from      = $request->from ?? null;
        $to        = $request->to ?? null;
        $branch_id = $request->branchOffice_id ?? null;
        $name      = "Productos";
        // Buscar el producto
        $product = Product::find($idproducto);

        if ($product) {
            //$name = preg_replace('/[^A-Za-z0-9_-]/', '_', $product->description);
        }
        // Generar el nombre del archivo
        $fileName = "Kardex_{$name}_" . now()->format('Ymd') . ".xlsx";

        // Retornar la exportación
        return Excel::download(new KardexExport($idproducto, $from, $to, $branch_id), $fileName);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/ticket-carga/{id}",
     *     summary="Generar ticket de carga",
     *     tags={"CargaDocument"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del documento de carga",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket de carga generado correctamente",
     *         @OA\Header(
     *             header="Content-Disposition",
     *             @OA\Schema(type="string", example="attachment; filename=ticket.pdf")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     )
     * )
     */

    public function ticketcarga(Request $request, $idMov = 10)
    {
        $doc_carga = CargaDocument::find($idMov);

        if (! $doc_carga) {
            abort(404, 'Documento Carga No Encontrado');
        }

        $data = [
            "doc_carga"    => $doc_carga,
            "details"    => DocumentCargaDetail::where('document_carga_id',$doc_carga->id)->get()?? [],
            "branchoffice" => $doc_carga->branchOffice,
        ];

        $pdf = Pdf::loadView('carga-almacen-ticket', $data);
        $pdf->setPaper([15, 5, 172, 450], 'portrait');

        $fileName = 'Ticket-carga-' . str_replace(' ', '_', $doc_carga->code_doc) . '.pdf';

        return $pdf->download($fileName);
    }

}
