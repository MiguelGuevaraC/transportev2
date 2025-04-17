<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeCarroceria;
use Illuminate\Http\Request;

class TypeCarroceriaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transporte/public/api/typecarroceria",
     *     summary="Retrieve all Type Carrocerias",
     *     tags={"Type Carroceria"},
     *     description="Fetches a list of all available Type Carrocerias in the system.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of the list of Type Carrocerias.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TypeCarroceria")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. The user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $list = TypeCarroceria::with(['typeCompany'])->where('state', 1)->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/typecarroceria",
 *     summary="Create a new Type Carroceria",
 *     tags={"Type Carroceria"},
 *     description="Stores a new Type Carroceria in the database.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating a Type Carroceria",
 *         @OA\JsonContent(
 *             required={"description", "state", "typecompany_id"},
 *             @OA\Property(property="description", type="string", example="Box Truck", description="Description of the Type Carroceria"),

 *             @OA\Property(property="typecompany_id", type="integer", example=1, description="ID of the associated Type Company")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Carroceria created successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/TypeCarroceria"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Some fields are required.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized. The user is not authenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'description' => 'required|string|max:255',
            'typecompany_id' => 'required|integer|exists:type_companies,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'description' => $request->input('description'),
            'typecompany_id' => $request->input('typecompany_id'),

        ];

        $object = TypeCarroceria::create($data);

        $object = TypeCarroceria::with(['typeCompany'])->find($object->id);

        return response()->json($object, 200);
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/typecarroceria/{id}",
 *     summary="Get a Type Carroceria by ID",
 *     tags={"Type Carroceria"},
 *     description="Retrieve a Type Carroceria by its ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Type Carroceria to retrieve",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Carroceria found",
 *         @OA\JsonContent(
 *             type="object",
 *             ref="#/components/schemas/TypeCarroceria"
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Type Carroceria not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Carroceria not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function show($id)
    {
        $typeCarroceria = TypeCarroceria::with(['typeCompany'])->find($id);
        if (!$typeCarroceria) {
            return response()->json(['message' => 'Type Carroceria not found'], 404);
        }
        return response()->json($typeCarroceria, 200);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/typecarroceria/{id}",
 *     summary="Update an existing Type Carroceria",
 *     tags={"Type Carroceria"},
 *     description="Update an existing Type Carroceria",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Type Carroceria to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Updated Type Carroceria data",
 *         @OA\JsonContent(
 *             @OA\Property(property="description", type="string", example="Updated Description", description="Description of the Type Carroceria"),
 *             @OA\Property(property="typecompany_id", type="integer", example=1, description="ID of the associated Type Company"),

 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Carroceria updated successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/TypeCarroceria"
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
 *     @OA\Response(
 *         response=404,
 *         description="Type Carroceria not found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Carroceria not found.")
 *         )
 *     )
 * )
 */
    public function update(Request $request, $id)
    {
        $object = TypeCarroceria::with(['typeCompany'])->find($id);
        if (!$object) {
            return response()->json(['message' => 'Type Carroceria not found'], 404);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'description' => 'required|string|max:255',
            'typecompany_id' => 'required|integer|exists:type_companies,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'description' => $request->input('description'),
            'typecompany_id' => $request->input('typecompany_id'),

        ];

        $object->update($data);
        $object = TypeCarroceria::with(['typeCompany'])->find($object->id);

        return response()->json($object, 200);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/typecarroceria/{id}",
 *     summary="Delete a Type Carroceria",
 *     tags={"Type Carroceria"},
 *     description="Delete a Type Carroceria by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Type Carroceria to delete",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Carroceria deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Carroceria deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Type Carroceria not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Carroceria not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
        $object = TypeCarroceria::with(['typeCompany'])->find($id);
        if (!$object) {
            return response()->json(['message' => 'Type Carroceria not found'], 404);
        }

        $object->state = 0;
        $object->save();

        return response()->json(['message' => 'Type Carroceria deleted successfully'], 200);
    }

}
