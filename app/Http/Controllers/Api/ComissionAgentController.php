<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommisionAgent;
use Illuminate\Http\Request;

class ComissionAgentController extends Controller
{

/**
 *
 * @return \Illuminate\Http\Response
 *
 * @OA\Get(
 *     path="/transportedev/public/api/comissionAgent",
 *     summary="Get all comission",
 *     tags={"ComissionAgent"},
 *     description="Show all comission",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of comission",
 *        @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Comission_Agent")
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
        $persons = CommisionAgent::with(["person"])->orderBy('id', 'desc')->get();
        return response()->json($persons, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/comissionAgent",
     *     summary="Store a new comissionAgent",
     *     tags={"ComissionAgent"},
     *     description="Create a new comissionAgent",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Comission_Agent data",
     *     @OA\JsonContent(
     *         @OA\Property(property="paymentComission", type="string", example="100", description="paymentComission Type of User"),
     *@OA\Property(property="person_id", type="integer", example="1", description="persona_id Type of User"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Comission_Agent created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Comission_Agent")
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
            'paymentComission' => 'required',
            'person_id' => 'required|exists:people,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $personAgentComission = CommisionAgent::where("person_id", $request->input('person_id'))->first();

        if ($personAgentComission) {
            return response()->json(['error' => 'This person already a comission agent.'], 422);
        }

        $data = [
            'paymentComission' => $request->input('paymentComission'),
            'person_id' => $request->input('person_id'),
        ];

        $object = CommisionAgent::create($data);
        // $object = CommisionAgent::with(['permissions',"permissions.groupMenu"])->find($object->id);
        $object = CommisionAgent::with(["person"])->find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/comissionAgent/{id}",
     *     summary="Get a comissionAgent by ID",
     *     tags={"ComissionAgent"},
     *     description="Retrieve a comissionAgent by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comissionAgent to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Comission Agent encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/Comission_Agent"
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
        $comissionAgent = CommisionAgent::with(["person"])->find($id);
        if (!$comissionAgent) {
            return response()->json(['message' => 'Comission Agent not found'], 422);
        }
        return response()->json($comissionAgent, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/comissionAgent/{id}",
     *     summary="Update an existing comissionAgent",
     *     tags={"ComissionAgent"},
     *     description="Update an existing comissionAgent",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comissionAgent to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Comission Agent data",
     *     @OA\JsonContent(
     *                    @OA\Property(property="paymentComission", type="string", example="100", description="paymentComission Type of User"),
     *@OA\Property(property="person_id", type="integer", example="1", description="persona_id Type of User"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Comission Agent updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Comission_Agent")
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
        $object = CommisionAgent::find($id);
        if (!$object) {
            return response()->json(['message' => 'Comission Agent not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'paymentComission' => 'required',
            'person_id' => 'required|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'paymentComission' => $request->input('paymentComission'),
            'person_id' => $request->input('person_id'),
        ], $filterNullValues);

        $object->update($Data);
        $object = CommisionAgent::with(["person"])->find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/comissionAgent/{id}",
     *     summary="Delete a comissionAgent",
     *     tags={"ComissionAgent"},
     *     description="Delete a comissionAgent by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comissionAgent to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comision Agent deleted successfully",
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
        $comissionAgent = CommisionAgent::find($id);
        if (!$comissionAgent) {
            return response()->json(['message' => 'Comision Agent not found'], 422);
        }
        $comissionAgent->delete();
    }

}
