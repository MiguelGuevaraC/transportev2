<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/place",
     *     summary="Get all Place",
     *     tags={"Place"},
     *     description="Show all Place",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Place"
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
         $list = Place::with(['district.province.department'])->orderBy('name', 'desc')->get();
         return response()->json($list, 200);
     }
 
    /**
     * @OA\Post(
     *     path="/transportev2/public/api/place",
     *     summary="Store a new place",
     *     tags={"Place"},
     *     description="Create a new place",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Place data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Direccion 1", description="Name of the place"),
     *             @OA\Property(property="address", type="string", example="123 Main St", description="Address of the place"),
     *             @OA\Property(property="district_id", type="integer", example=1, description="ID of the district")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place created successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Place"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Some Fields are required.")
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
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                Rule::unique('places', 'name')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            // 'address' => 'required',
            // 'district_id' => 'required|exists:districts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $district = District::find($request->input('district_id'));

        $data = [
            'name' => $request->input('name'),
            // 'ubigeo' => $district->ubigeo_code,
            // 'address' => '', //$request->input('address')
            // 'district_id' => $district->id,
        ];

        $object = Place::create($data);

        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/place/{id}",
     *     summary="Get a place by ID",
     *     tags={"Place"},
     *     description="Retrieve a place by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the place to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Place encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/Place"
     *)
     *),

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
        $place = Place::find($id);
        if (!$place) {
            return response()->json(['message' => 'Place not found'], 422);
        }
        $place = Place::with(['district.province.department'])->find($id);
        return response()->json($place, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/place/{id}",
     *     summary="Update an existing place",
     *     tags={"Place"},
     *     description="Update an existing place",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the place to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Place data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Direccion 1", description="Name of the place"),
     *             @OA\Property(property="address", type="string", example="123 Main St", description="Address of the place"),
     *             @OA\Property(property="district_id", type="integer", example=1, description="ID of the district")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place updated successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Place"
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
     *         description="Place not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Place not found.")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $object = Place::find($id);
        if (!$object) {
            return response()->json(['message' => 'Place not found'], 404);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                Rule::unique('places', 'name')->ignore($id)->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            // 'address' => 'required',
            // 'district_id' => 'required|exists:districts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $district = District::find($request->input('district_id'));

        $data = [
            'name' => $request->input('name'),
            // 'ubigeo' => '',//$district->ubigeo_code,
            // 'address' => '', //$request->input('address')
            // 'district_id' => $district->id,
        ];

        $object->update($data);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/place/{id}",
     *     summary="Delete a place",
     *     tags={"Place"},
     *     description="Delete a place by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the place to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place deleted successfully",
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
        $place = Place::find($id);
        if (!$place) {
            return response()->json(['message' => 'Place not found'], 422);
        }
        $place->delete();
    }
}
