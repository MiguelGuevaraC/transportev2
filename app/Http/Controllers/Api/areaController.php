<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class areaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transportev2/public/api/area",
     *     summary="Get all area",
     *     tags={"Area"},
     *     description="Show all area",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of area",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Area")
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
        $list = Area::orderBy('id', 'desc')
        ->where('state', 1)->get();
        return response()->json($list, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/transportev2/public/api/area",
     *     summary="Create a new area",
     *     tags={"Area"},
     *     description="Create a new area",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "state"},
     *             @OA\Property(property="name", type="string", example="Area"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Area created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Area")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Invalid data provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Duplicate Name",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Area name already exists.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => 'required|string|max:255|unique:areas,name',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $data = [
            'name' => $request->input('name'),
        ];
        $area = Area::create($data);

        return response()->json($area, 201);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/area/{id}",
     *     summary="Get a area by ID",
     *     tags={"Area"},
     *     description="Retrieve a area by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the area to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Area found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/Area"
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
        $object = Area::find($id);
        if (!$object) {
            return response()->json(['message' => 'Area not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *     path="/transportev2/public/api/area/{id}",
     *     summary="Update an existing area",
     *     tags={"Area"},
     *     description="Update an existing area",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the area to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "state"},
     *             @OA\Property(property="name", type="string", example="Area2"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Area updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Area")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Area not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Duplicate Name",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Area name already exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Invalid data provided.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $area = Area::find($id);

        if (!$area) {
            return response()->json(['msg' => 'Area not found.'], 404);
        }

        $validator = validator()->make($request->all(), [
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $data = [
            'name' => $request->input('name'),
        ];
        $area->update($data);

        return response()->json($area, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/area/{id}",
     *     summary="Delete a Area",
     *     tags={"Area"},
     *     description="Delete a Area by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Area to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Area deleted successfully",
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
        $object = Area::find($id);
        if (!$object) {
            return response()->json(['message' => 'Area not found'], 422);
        }
        $object->state = 0;
        $object->save();
    }
}
