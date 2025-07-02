<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FleetController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transporte/public/api/fleet",
     *     summary="Get all fleet",
     *     tags={"Fleet"},
     *     description="Show all fleet",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of fleet",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Fleet")
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
    // public function index()
    // {
    //     $list = Fleet::orderBy('id', 'desc')->get();
    //     return response()->json($list, 200);
    // }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/fleet",
     *     summary="Store a new fleet",
     *     tags={"Fleet"},
     *     description="Create a new fleet",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Fleet data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Fleet", description="Model Functional"),
     *          @OA\Property(property="abbreviation", type="string", example="abbreviation", description="abbreviation"),
     *          @OA\Property(property="reference", type="string", example="reference", description="reference"),

     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Fleet created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Fleet")
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
    // public function store(Request $request)
    // {

    //     $validator = validator()->make($request->all(), [
    //         'name' => [
    //             'required',
    //             Rule::unique('fleets', 'name')->whereNull('deleted_at'),
    //         ],

    //         'abbreviation' => [
    //             'required',
    //             Rule::unique('fleets', 'abbreviation')->whereNull('deleted_at'),
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()->first()], 422);
    //     }

    //     $data = [
    //         'name' => $request->input('name'),
    //         'abbreviation' => $request->input('abbreviation'),
    //         'reference' => $request->input('reference'),
    //     ];

    //     $object = Fleet::create($data);
    //     $object = Fleet::find($object->id);
    //     return response()->json($object, 200);

    // }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/fleet/{id}",
     *     summary="Get a fleet by ID",
     *     tags={"Fleet"},
     *     description="Retrieve a fleet by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the fleet to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fleet found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/Fleet"
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
    // public function show($id)
    // {
    //     $object = Fleet::find($id);
    //     if (!$object) {
    //         return response()->json(['message' => 'Fleet not found'], 422);
    //     }

    //     return response()->json($object, 200);
    // }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/fleet/{id}",
     *     summary="Update an existing Fleet",
     *     tags={"Fleet"},
     *     description="Update an existing Fleet",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Fleet to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Fleet data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Fleet2", description="Model Functional"),
     *          @OA\Property(property="abbreviation", type="string", example="abbreviation", description="abbreviation"),
     *          @OA\Property(property="reference", type="string", example="reference", description="reference"),

     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Fleet")
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

    // public function update(Request $request, $id)
    // {
    //     $object = Fleet::find($id);
    //     if (!$object) {
    //         return response()->json(['message' => 'Fleet not found'], 422);
    //     }

    //     $validator = validator()->make($request->all(), [
    //         'name' => [
    //             'required',
    //             Rule::unique('fleets', 'name')
    //                 ->ignore($object->id)
    //                 ->whereNull('deleted_at'),
    //         ],

    //         'abbreviation' => [
    //             'required',
    //             Rule::unique('fleets', 'abbreviation')
    //                 ->ignore($object->id)
    //                 ->whereNull('deleted_at'),
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()->first()], 422);
    //     }

    //     $filterNullValues = function ($value) {
    //         return $value !== null || $value === false;
    //     };

    //     $routeData = array_filter([
    //         'name' => $request->input('name'),
    //         'abbreviation' => $request->input('abbreviation'),
    //         'reference' => $request->input('reference'),

    //     ], $filterNullValues);

    //     $object->update($routeData);

    //     // $object = Role::with(['permissions',"permissions.groupMenu"])->find($id);
    //     $object = Fleet::find($object->id);

    //     return response()->json($object, 200);
    // }
    /**
     * @OA\Delete(
     *     path="/transporte/public/api/fleet/{id}",
     *     summary="Delete a Fleet",
     *     tags={"Fleet"},
     *     description="Delete a Fleet by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Fleet to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fleet deleted successfully",
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

    // public function destroy($id)
    // {
    //     $object = Fleet::find($id);
    //     if (!$object) {
    //         return response()->json(['message' => 'Fleet not found'], 422);
    //     }
    //     $object->delete();
    // }
}
