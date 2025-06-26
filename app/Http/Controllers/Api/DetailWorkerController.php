<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailWorker;
use App\Models\Programming;
use Illuminate\Http\Request;

class DetailWorkerController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailWorker",
     *     summary="Get all detailWorker",
     *     tags={"DetailWorker"},
     *     description="Show all detailWorker",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of detailWorker",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/DetailWorker")
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
        $list = DetailWorker::orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/detailWorker",
     *     summary="Create a new detailWorker",
     *     tags={"DetailWorker"},
     *     description="Create a new detailWorker",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail Worker data",
     *         @OA\JsonContent(
     *                     @OA\Property(property="function", type="string", example="driver"),
     *                      @OA\Property(property="worker_id", type="integer", example=1),
     *                      @OA\Property(property="programming_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail Worker created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Detail Worker created successfully")
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

            'function' => 'required',
            'programming_id' => 'required|exists:programmings,id',
            'worker_id' => 'required|exists:workers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'function' => $request->input('function'),
            'programming_id' => $request->input('programming_id') ?? null,
            'worker_id' => $request->input('worker_id') ?? null,
        ];

        $object = DetailWorker::create($data);

        $object = DetailWorker::find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/detailWorker/{id}",
     *     summary="Update an existing detailWorker",
     *     tags={"DetailWorker"},
     *     description="Update an existing detailWorker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailWorker to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail Worker data",
     *         @OA\JsonContent(
     *                     @OA\Property(property="function", type="string", example="driver"),
     *                      @OA\Property(property="worker_id", type="integer", example=1),
     *                      @OA\Property(property="programming_id", type="integer", example=1),
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
        $object = DetailWorker::find($id);
        if (!$object) {
            return response()->json(['message' => 'DetailWorker not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'function' => 'nullable',
            'programming_id' => 'nullable|exists:programmings,id',
            'worker_id' => 'nullable|exists:workers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'function' => $request->input('function'),
            'programming_id' => $request->input('programming_id'),
            'worker_id' => $request->input('worker_id'),
        ], $filterNullValues);

        $object->update($Data);

        $object = DetailWorker::find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailWorker/{id}",
     *     summary="Get a detailWorker by ID",
     *     tags={"DetailWorker"},
     *     description="Retrieve a detailWorker by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailWorker to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DetailWorker found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/DetailWorker"
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
        $object = DetailWorker::find($id);
        if (!$object) {
            return response()->json(['message' => 'Detail Worker not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailWorkerForProgramming/{id}",
     *     summary="Get a detailWorker by ID",
     *     tags={"DetailWorker"},
     *     description="Retrieve a detailWorker by its ID Programming",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the programming to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DetailWorkers found by Programming",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/DetailWorker"
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
    public function showForProgramming($id)
    {
        $object = Programming::find($id);
        if (!$object) {
            return response()->json(['message' => 'Prgramming not found'], 422);
        }
        $list = DetailWorker::where('programming_id', $id)->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/detailWorker/{id}",
     *     summary="Delete a DetailWorker",
     *     tags={"DetailWorker"},
     *     description="Delete a DetailWorker by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the DetailWorker to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail Worker deleted successfully",
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
        $object = DetailWorker::find($id);
        if (!$object) {
            return response()->json(['message' => 'Detail Worker not found'], 422);
        }
        $object->delete();
    }
}
