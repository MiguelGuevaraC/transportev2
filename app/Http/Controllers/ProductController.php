<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest\IndexProductRequest;
use App\Http\Requests\ProductRequest\StoreProductRequest;
use App\Http\Requests\ProductRequest\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\CarrierGuide;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $ProductService)
    {
        $this->productService = $ProductService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/product",
 *     summary="Obtener información de Productos con filtros y ordenamiento",
 *     tags={"Product"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="codeproduct", in="query", description="Filtrar por código del producto", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="description", in="query", description="Filtrar por descripción", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="person_id", in="query", description="Filtrar por ID de persona", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="addressproduct", in="query", description="Filtrar por dirección del producto", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="category", in="query", description="Filtrar por categoría", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="unity_id", in="query", description="Filtrar por ID de unidad de medida", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="weight", in="query", description="Filtrar por peso", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Lista de Productos", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */


    public function index(IndexProductRequest $request)
    {
        $branchOffice_id = Auth::user()->worker->branchOffice_id;
        $carrier         = CarrierGuide::findOrFail(4014);
        $id_branch       = $carrier->reception->branchOffice_id;
        $carrier->reception->details->each(function ($detail) use ($id_branch) {
            if (isset($detail->product_id)) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $this->productService->updatestock($product, $id_branch);
                }
            }
        });
        // Obtener los productos filtrados
        $products = $this->getFilteredResults(
            Product::class,
            $request,
            Product::filters,
            Product::sorts,
            ProductResource::class
        );
        $items = $products instanceof \Illuminate\Pagination\AbstractPaginator  ? $products->items() : $products;
        collect($items)->each(function ($product) use ($branchOffice_id) {
            if (isset($product->id)) {
                $productModel = Product::find($product->id);
                if ($productModel) {
                    $this->productService->updatestock($productModel, $branchOffice_id);
                }
            }
        });
        return $products;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/product/{id}",
 *     summary="Obtener detalles de un Product por ID",
 *     tags={"Product"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Product", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Product encontrado", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=404, description="Producto no encontrado", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Producto no encontrado")))
 * )
 */

    public function show($id)
    {

        $producto = $this->productService->getProductById($id);

        if (! $producto) {
            return response()->json([
                'error' => 'Producto No Encontrado',
            ], 404);
        }

        return new ProductResource($producto);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/product",
 *     summary="Crear Product",
 *     tags={"Product"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/ProductRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
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

    public function store(StoreProductRequest $request)
    {
        $producto = $this->productService->createProduct($request->validated());
        return new ProductResource($producto);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/product/{id}",
 *     summary="Actualizar un Product",
 *     tags={"Product"},
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
 *             @OA\Schema(ref="#/components/schemas/ProductRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
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
 *         description="Producto no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Producto no encontrado")
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

    public function update(UpdateProductRequest $request, $id)
    {

        $validatedData = $request->validated();

        $producto = $this->productService->getProductById($id);
        if (! $producto) {
            return response()->json([
                'error' => 'Producto No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->productService->updateProduct($producto, $validatedData);
        return new ProductResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/product/{id}",
 *     summary="Eliminar un Productpor ID",
 *     tags={"Product"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Product eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Product eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Producto no encontrado"))),

 * )
 */

    public function destroy($id)
    {

        $proyect = $this->productService->getProductById($id);

        if (! $proyect) {
            return response()->json([
                'error' => 'Producto No Encontrado.',
            ], 404);
        }
        $proyect = $this->productService->destroyById($id);

        return response()->json([
            'message' => 'Producto eliminado exitosamente',
        ], 200);
    }

}
