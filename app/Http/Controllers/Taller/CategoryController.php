<?php
namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest\IndexCategoryRequest;
use App\Http\Requests\CategoryRequest\StoreCategoryRequest;
use App\Http\Requests\CategoryRequest\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $CategoryService)
    {
        $this->categoryService = $CategoryService;
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/category",
 *     summary="Obtener información de Categorys con filtros y ordenamiento",
 *     tags={"Category"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Categorys", @OA\JsonContent(ref="#/components/schemas/Category")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexCategoryRequest $request)
    {
        return $this->getFilteredResults(
            Category::class,
            $request,
            Category::filters,
            Category::sorts,
            CategoryResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/category/{id}",
 *     summary="Obtener detalles de un Categoria por ID",
 *     tags={"Category"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Category", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Categoria encontrado", @OA\JsonContent(ref="#/components/schemas/Category")),
 *     @OA\Response(response=404, description="Categoria no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Categoria no encontrada")))
 * )
 */

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if (! $category) {
            return response()->json([
                'error' => 'Categoria No Encontrada',
            ], 404);
        }
        return new CategoryResource($category);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/category",
 *     summary="Crear Category",
 *     tags={"Category"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CategoryRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Categoria creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
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

    public function store(StoreCategoryRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $category       = $this->categoryService->createCategory($data);
        return new CategoryResource($category);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/category/{id}",
 *     summary="Actualizar un Category",
 *     tags={"Category"},
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
 *             @OA\Schema(ref="#/components/schemas/CategoryRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Categoria actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
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
 *         description="Categoria no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Categoria no encontrada")
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

    public function update(UpdateCategoryRequest $request, $id)
    {
        $validatedData = $request->validated();
        $category      = $this->categoryService->getCategoryById($id);
        if (! $category) {
            return response()->json([
                'error' => 'Categoria No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->categoryService->updateCategory($category, $validatedData);
        return new CategoryResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/category/{id}",
 *     summary="Eliminar un Categorypor ID",
 *     tags={"Category"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Categoria eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Categoria eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Categoria no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if (! $category) {
            return response()->json([
                'error' => 'Categoria No Encontrada.',
            ], 404);
        }

        if ($category->repuestos()->exists()) {
            return response()->json([
                'error' => 'La categoría tiene productos asociados.',
            ], 422);
        }

        $category = $this->categoryService->destroyById($id);
        return response()->json([
            'message' => 'Categoria eliminado exitosamente',
        ], 200);
    }
}
