<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Person;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/address",
     *     summary="Get all Address",
     *     tags={"Address"},
     *     description="Show all Address",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Address"
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
        $list = Address::with('client')->where('state', '1')->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/addressForPerson/{idPersona}",
     *     summary="Get Address for Person",
     *     tags={"Address"},
     *     description="Show all Address",
     *     security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="idPersona",
     *         in="path",
     *         description="ID of the person",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Address"
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

    public function addressForPerson($idPersona)
    {
        $list = Address::where('client_id', $idPersona)->where('state', '1')->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/address",
     *     summary="Store a new address",
     *     tags={"Address"},
     *     description="Create a new address",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Address data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Direccion 1", description="Direction of User"),
     *@OA\Property(property="reference", type="string", example="Reference 1", description="Reference of User"),
     *@OA\Property(property="client_id", type="integer", example=1, description="Id person of User"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Address created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Address")
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
            'name' => 'required',
            'client_id' => 'required|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'reference' => $request->input('reference'),
            'client_id' => $request->input('client_id'),
        ];

        $object = Address::create($data);

        $object = Address::with(["client"])->find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/address/{id}",
     *     summary="Get a address by ID",
     *     tags={"Address"},
     *     description="Retrieve a address by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the address to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Address encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/Address"
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
        $address = Address::with(["client"])->find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 422);
        }
        return response()->json($address, 200);
    }
    /**
     * @OA\Put(
     *     path="/transportev2/public/api/address/{id}",
     *     summary="Update an existing address",
     *     tags={"Address"},
     *     description="Update an existing address",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the address to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Address data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Direccion 1", description="Direction of User"),
     *@OA\Property(property="reference", type="string", example="Reference 1", description="Reference of User"),
     *@OA\Property(property="client_id", type="integer", example=1, description="Id person of User"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Address updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Address")
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
        $object = Address::find($id);
        if (!$object) {
            return response()->json(['message' => 'Address not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => 'required',
            'client_id' => 'required|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'name' => $request->input('name'),
            'reference' => $request->input('reference'),
            'client_id' => $request->input('client_id'),

        ], $filterNullValues);

        $object->update($Data);
        $object = Address::with(["client"])->find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/address/{id}",
     *     summary="Delete a address",
     *     tags={"Address"},
     *     description="Delete a address by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the address to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address deleted successfully",
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
         // Buscar la dirección
         $address = Address::find($id);
     
         // Si no existe, devolver un error
         if (!$address) {
             return response()->json(['message' => 'Dirección No Encontrada'], 422);
         }
     
         // Validar si la dirección está asociada como punto de envío o destino
         $hasReceptions = $address->receptionsAsSender()->exists() || $address->receptionsAsDestination()->exists();
     
         if ($hasReceptions) {
             return response()->json(['message' => 'Dirección está asociada con recepciones'], 422);
         }
     
         // Cambiar el estado de la dirección a 0 (deshabilitada)
         $address->state = 0;
         $address->save();
     
         return response()->json(['data' =>$address], 200);
     }
}
