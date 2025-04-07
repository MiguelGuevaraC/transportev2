<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeCompany;
use Illuminate\Http\Request;

class TypeCompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transportedev/public/api/typecompany",
     *     summary="Retrieve all Type Companies",
     *     tags={"Type Company"},
     *     description="Fetches a list of all available Type Companies in the system.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of the list of Type Companies.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TypeCompany")
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
        $list = TypeCompany::where('state', 1)->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/typecompany",
 *     summary="Create a new Type Company",
 *     tags={"Type Company"},
 *     description="Stores a new Type Company in the database.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating a Type Company",
 *         @OA\JsonContent(
 *             required={"description", "state"},
 *             @OA\Property(property="description", type="string", example="Transportation Company", description="Description of the Type Company"),

 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Company created successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/TypeCompany"
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

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'description' => $request->input('description'),
        ];

        $object = TypeCompany::create($data);

        $object= $this->show($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/typecompany/{id}",
     *     summary="Get a Type Company by ID",
     *     tags={"Type Company"},
     *     description="Retrieve a Type Company by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Type Company to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type Company found",
     *         @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/TypeCompany"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type Company not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Type Company not found")
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
        $typeCompany = TypeCompany::find($id);
        if (!$typeCompany) {
            return response()->json(['message' => 'Type Company not found'], 404);
        }
        return response()->json($typeCompany, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/typecompany/{id}",
     *     summary="Update an existing Type Company",
     *     tags={"Type Company"},
     *     description="Update an existing Type Company",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Type Company to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated Type Company data",
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="Updated Description", description="Description of the Type Company"),

     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type Company updated successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TypeCompany"
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
     *         description="Type Company not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Type Company not found.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $object = TypeCompany::find($id);
        if (!$object) {
            return response()->json(['message' => 'Type Company not found'], 404);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'description' => 'required|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'description' => $request->input('description'),
        ];

        $object->update($data);
        $object= $this->show($object->id);

        return response()->json($object, 200);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/typecompany/{id}",
 *     summary="Delete a Type Company",
 *     tags={"Type Company"},
 *     description="Delete a Type Company by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Type Company to delete",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Type Company deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Company deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Type Company not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Type Company not found.")
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
    public function destroy($id)
    {
        $typeCompany = TypeCompany::find($id);
        if (!$typeCompany) {
            return response()->json(['message' => 'Type Company not found'], 404);
        }
        $typeCompany->state = 0;
        $typeCompany->save();

        return response()->json(['message' => 'Type Company deleted successfully.'], 200);
    }

}
