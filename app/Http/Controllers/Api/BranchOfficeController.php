<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchOffice;
use App\Models\Person;
use Illuminate\Http\Request;

class BranchOfficeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transportedev/public/api/branchOffice",
     *     summary="Get all branchOffice",
     *     tags={"BranchOffice"},
     *     description="Show all branchOffice",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of branchOffice",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BranchOffice")
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
        $list = BranchOffice::orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/branchOffice",
     *     summary="Store a new branchOffice",
     *     tags={"BranchOffice"},
     *     description="Create a new branchOffice",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="BranchOffice data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Sucursal Lambayeque", description="Name branch office"),
     * @OA\Property(property="location", type="string", example="Ciudad Lambayeque", description="Location branch office"),
     * @OA\Property(property="address", type="string", example="Av. Insurgentes Sur 12345", description="Address branch office"),

     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="BranchOffice created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BranchOffice")
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
            'name' => 'required|unique:branch_offices,name',
            'location' => 'required',
            'address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'location' => $request->input('location'),
            'address' => $request->input('address'),
        ];

        $object = BranchOffice::create($data);
        $object = BranchOffice::find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/branchOffice/{id}",
     *     summary="Get a branchOffice by ID",
     *     tags={"BranchOffice"},
     *     description="Retrieve a branchOffice by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the branchOffice to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BranchOffice found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/BranchOffice"
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
        $object = BranchOffice::find($id);
        if (!$object) {
            return response()->json(['message' => 'Branch Office not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/branchOffice/{id}",
     *     summary="Update an existing BranchOffice",
     *     tags={"BranchOffice"},
     *     description="Update an existing BranchOffice",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the BranchOffice to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Sucursal Lambayeque2", description="Name branch office"),
     * @OA\Property(property="location", type="string", example="Ciudad Lambayeque2", description="Location branch office"),
     * @OA\Property(property="address", type="string", example="Av. Insurgentes Sur 12345", description="Address branch office"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BranchOffice")
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
        $object = BranchOffice::find($id);
        if (!$object) {
            return response()->json(['message' => 'BranchOffice not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => 'required|unique:branch_offices,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $routeData = array_filter([
            'name' => $request->input('name'),
            'location' => $request->input('location'),
            'address' => $request->input('address'),

        ], $filterNullValues);

        $object->update($routeData);

        // $object = Role::with(['permissions',"permissions.groupMenu"])->find($id);
        $object = BranchOffice::find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/branchOffice/{id}",
     *     summary="Delete a BranchOffice",
     *     tags={"BranchOffice"},
     *     description="Delete a BranchOffice by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the BranchOffice to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Branch Office deleted successfully",
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
         // Encuentra la oficina de sucursal por ID
         $object = BranchOffice::find($id);

         // Verifica si la sucursal no fue encontrada
         if (!$object) {
             return response()->json(['message' => 'Branch Office not found'], 422);
         }

         // Verifica si hay al menos una persona asociada a esta sucursal
         $hasPeople = Person::where('branchOffice_id', $id)->exists();

         if ($hasPeople) {
             return response()->json(['message' => 'Branch Office has people'], 409);
         }

         // Si no hay personas asociadas, elimina la sucursal
         $object->delete();

         // Devuelve una respuesta de éxito si la eliminación fue correcta
         return response()->json(['message' => 'Branch Office deleted successfully'], 200);
     }

}
