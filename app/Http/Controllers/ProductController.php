<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest\IndexProductRequest;
use App\Http\Requests\ProductRequest\StoreProductRequest;
use App\Http\Requests\ProductRequest\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $ProductService)
    {
        $this->productService = $ProductService;
    }

/**
 * @OA\Get(
 *     path="/transportev2/public/api/product",
 *     summary="Obtener información de Products con filtros y ordenamiento",
 *     tags={"Product"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="description", in="query", description="Filtrar por descripción", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="stock", in="query", description="Filtrar por stock", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="weight", in="query", description="Filtrar por peso", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="category", in="query", description="Filtrar por categoría", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="unity", in="query", description="Filtrar por unidad de medida", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="unity_id", in="query", description="Filtrar por ID de unidad", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="person_id", in="query", description="Filtrar por ID de persona", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Lista de Products", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexProductRequest $request)
    {
        // Obtener los productos filtrados
        $products = $this->getFilteredResults(
            Product::class,
            $request,
            Product::filters,
            Product::sorts,
            ProductResource::class
        );

        // Mapeo para actualizar el stock de cada producto
        $products->map(function ($product) {
            $this->productService->updatestock(Product::find($product->id));
            return $product; // Es importante devolver el producto actualizado
        });

        return $products;
    }

/**
 * @OA\Get(
 *     path="/transportev2/public/api/product/{id}",
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
        $this->productService->updatestock(Product::find($producto->id));
        
        return new ProductResource($producto);
    }

/**
 * @OA\Post(
 *     path="/transportev2/public/api/product",
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
 *     path="/transportev2/public/api/product/{id}",
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
 *     path="/transportev2/public/api/product/{id}",
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
