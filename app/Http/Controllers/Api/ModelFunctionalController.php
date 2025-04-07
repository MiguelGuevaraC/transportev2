<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModelFunctional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModelFunctionalController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/modelFunctional",
     *     summary="Get all modelFunctional",
     *     tags={"ModelFunctional"},
     *     description="Show all modelFunctional",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of modelFunctional",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Model_functional")
     *         )
     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function index()
    {
        $list = ModelFunctional::orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/modelFunctional",
     *     summary="Store a new modelFunctional",
     *     tags={"ModelFunctional"},
     *     description="Create a new modelFunctional",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="ModelFunctional data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Model Functional", description="Model Functional"),
     *          @OA\Property(property="abbreviation", type="string", example="abbreviation", description="abbreviation"),
     *          @OA\Property(property="reference", type="string", example="reference", description="reference"),

     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="ModelFunctional created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Model_functional")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some Fields are required.")
     *         )
     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                Rule::unique('model_functionals', 'name')->whereNull('deleted_at'),
            ],
            'abbreviation' => [
                'required',
                Rule::unique('model_functionals', 'abbreviation')->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'abbreviation' => $request->input('abbreviation'),
            'reference' => $request->input('reference'),
        ];

        $object = ModelFunctional::create($data);
        $object = ModelFunctional::find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/modelFunctional/{id}",
     *     summary="Get a modelFunctional by ID",
     *     tags={"ModelFunctional"},
     *     description="Retrieve a modelFunctional by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the modelFunctional to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ModelFunctional found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/Model_functional"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function show($id)
    {
        $object = ModelFunctional::find($id);
        if (!$object) {
            return response()->json(['message' => 'Model Functional not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/modelFunctional/{id}",
     *     summary="Update an existing Model_functional",
     *     tags={"ModelFunctional"},
     *     description="Update an existing Model_functional",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Model_functional to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Model_functional data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Model_functional2", description="Model Functional"),
     *          @OA\Property(property="abbreviation", type="string", example="abbreviation", description="abbreviation"),
     *          @OA\Property(property="reference", type="string", example="reference", description="reference"),

     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Model_functional")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *       @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function update(Request $request, $id)
    {
        $object = ModelFunctional::find($id);
        if (!$object) {
            return response()->json(['message' => 'ModelFunctional not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                Rule::unique('model_functionals', 'name')
                    ->ignore($object->id) // Ignora el registro actual en la validación
                    ->whereNull('deleted_at'),
            ],
            'abbreviation' => [
                'required',
                Rule::unique('model_functionals', 'abbreviation')
                    ->ignore($object->id) // Ignora el registro actual en la validación
                    ->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $routeData = array_filter([
            'name' => $request->input('name'),
            'abbreviation' => $request->input('abbreviation'),
            'reference' => $request->input('reference'),

        ], $filterNullValues);

        $object->update($routeData);

        // $object = Role::with(['permissions',"permissions.groupMenu"])->find($id);
        $object = ModelFunctional::find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/modelFunctional/{id}",
     *     summary="Delete a ModelFunctional",
     *     tags={"ModelFunctional"},
     *     description="Delete a ModelFunctional by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ModelFunctional to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model_functional deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function destroy($id)
    {
        $object = ModelFunctional::find($id);
        if (!$object) {
            return response()->json(['message' => 'Model Functional not found'], 422);
        }
        $object->delete();
    }
}
