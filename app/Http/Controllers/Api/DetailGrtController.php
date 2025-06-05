<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailGrt;
use Illuminate\Http\Request;

class DetailGrtController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailGrt",
     *     summary="Get all detailGrt",
     *     tags={"DetailGrt"},
     *     description="Show all detailGrt",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of detailGrt"
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
        $list = DetailGrt::with('carrierGuide', 'detailReception')->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/detailGrt",
     *     summary="Create a new detailGrt",
     *     tags={"DetailGrt"},
     *     description="Create a new detailGrt",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail GRT data",
     *         @OA\JsonContent(
     *          @OA\Property(property="carrierGuide_id", type="integer", example=1),
     *     @OA\Property(property="detailReception_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail GRT created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Detail GRT created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error: The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [

            'carrierGuide_id' => 'required|exists:carrier_guides,id',
            'detailReception_id' => 'required|exists:detail_receptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [

            'carrierGuide_id' => $request->input('carrierGuide_id') ?? null,
            'detailReception_id' => $request->input('detailReception_id'),
        ];

        $object = DetailGrt::create($data);

        $object = DetailGrt::with('carrierGuide', 'detailReception')->find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailGrt/{id}",
     *     summary="Get a detailGrt by ID",
     *     tags={"DetailGrt"},
     *     description="Retrieve a detailGrt by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailGrt to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DetailGrt found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/DetailGrt"
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
        $object = DetailGrt::with('carrierGuide', 'detailReception')->find($id);
        if (!$object) {
            return response()->json(['message' => 'Detail GRT not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/detailGrt/{id}",
     *     summary="Update an existing detailGrt",
     *     tags={"DetailGrt"},
     *     description="Update an existing detailGrt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailGrt to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail GRT data",
     *         @OA\JsonContent(
     *          @OA\Property(property="carrierGuide_id", type="integer", example=1),
     *     @OA\Property(property="detailReception_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail Reception updated successfully"
     *
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
        $object = DetailGrt::find($id);
        if (!$object) {
            return response()->json(['message' => 'DetailGrt not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [

            'carrierGuide_id' => 'nullable|exists:carrier_guides,id',
            'detailReception_id' => 'nullable|exists:detail_receptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'carrierGuide_id' => $request->input('carrierGuide_id'),
            'detailReception_id' => $request->input('detailReception_id'),

        ], $filterNullValues);

        $object->update($Data);

        $object = DetailGrt::with('carrierGuide', 'detailReception')->find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/detailGrt/{id}",
     *     summary="Delete a DetailGrt",
     *     tags={"DetailGrt"},
     *     description="Delete a DetailGrt by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the DetailGrt to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail GRT deleted successfully",
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
        $object = DetailGrt::find($id);
        if (!$object) {
            return response()->json(['message' => 'Detail GRT not found'], 422);
        }
        $object->delete();
    }
}
