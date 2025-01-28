<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transporte/public/api/contactInfo",
     *     summary="Get all Contact Info",
     *     tags={"ContactInfo"},
     *     description="Show all Contact Info",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Contact Info"
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
        $list = ContactInfo::with('person')->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/contactInfo",
     *     summary="Store a new contactInfo",
     *     tags={"ContactInfo"},
     *     description="Create a new contactInfo",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Worker data",
     *     @OA\JsonContent(
     *         @OA\Property(property="typeofDocument", type="string", example="dni", description="Type of document"),
     *         @OA\Property(property="documentNumber", type="string", example="12345678", description="Document number"),
     *         @OA\Property(property="names", type="string", example="Miguel", description="Names"),
     *         @OA\Property(property="fatherSurname", type="string", example="Doe", description="Father's surname"),
     *         @OA\Property(property="motherSurname", type="string", example="Smith", description="Mother's surname"),
     *         @OA\Property(property="address", type="string", example="123 Street Ave", description="address"),
     *         @OA\Property(property="telephone", type="string", example="123456789", description="Telephone"),
     *         @OA\Property(property="email", type="string", example="example@example.com", description="Email"),
     *         @OA\Property(property="person_id", type="integer", example=1, description="Person ID"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="ContactInfo created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContactInfo")
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
            'typeofDocument' => 'required',
            'documentNumber' => 'required',
            'names' => 'required',
            'fatherSurname' => 'required',
            'motherSurname' => 'required',
            'person_id' => 'required|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [

            'typeofDocument' => $request->input('typeofDocument'),
            'documentNumber' => $request->input('documentNumber'),
            'names' => $request->input('names'),
            'fatherSurname' => $request->input('fatherSurname'),
            'motherSurname' => $request->input('motherSurname'),

            'contactInfo' => $request->input('contactInfo') ?? null,
            'telephone' => $request->input('telephone') ?? null,
            'email' => $request->input('email') ?? null,

            'person_id' => $request->input('person_id'),

        ];

        $object = ContactInfo::create($data);

        $object = ContactInfo::with(["person"])->find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/contactInfo/{id}",
     *     summary="Get a contactInfo by ID",
     *     tags={"ContactInfo"},
     *     description="Retrieve a contactInfo by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the contactInfo to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="ContactInfo encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/ContactInfo"
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
        $contactInfo = ContactInfo::with(["person"])->find($id);
        if (!$contactInfo) {
            return response()->json(['message' => 'ContactInfo not found'], 422);
        }
        return response()->json($contactInfo, 200);
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/contactsForPerson/{idPersona}",
 *     summary="Get Contacts for Person",
 *     tags={"ContactInfo"},
 *     description="Show all Contacts",
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
 *         description="List of Contacts"
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

    public function contactsForPerson($idPersona)
    {
        $list = ContactInfo::where('person_id', $idPersona)->orderBy('names', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/contactInfo/{id}",
     *     summary="Update an existing contactInfo",
     *     tags={"ContactInfo"},
     *     description="Update an existing contactInfo",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the contactInfo to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Worker data",
     *     @OA\JsonContent(
     *         @OA\Property(property="typeofDocument", type="string", example="dni", description="Type of document"),
     *         @OA\Property(property="documentNumber", type="string", example="12345678", description="Document number"),
     *         @OA\Property(property="names", type="string", example="Miguel", description="Names"),
     *         @OA\Property(property="fatherSurname", type="string", example="Doe", description="Father's surname"),
     *         @OA\Property(property="motherSurname", type="string", example="Smith", description="Mother's surname"),
     *         @OA\Property(property="address", type="string", example="123 Street Ave", description="address"),
     *         @OA\Property(property="telephone", type="string", example="123456789", description="Telephone"),
     *         @OA\Property(property="email", type="string", example="example@example.com", description="Email"),
     *         @OA\Property(property="person_id", type="integer", example=1, description="Person ID"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="ContactInfo updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContactInfo")
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
        $object = ContactInfo::find($id);
        if (!$object) {
            return response()->json(['message' => 'ContactInfo not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'typeofDocument' => 'required',
            'documentNumber' => 'required',
            // 'names' => 'required',
            // 'fatherSurname' => 'required',
            // 'motherSurname' => 'required',
            'person_id' => 'required|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'typeofDocument' => $request->input('typeofDocument'),
            'documentNumber' => $request->input('documentNumber'),
            'names' => $request->input('names'),
            'fatherSurname' => $request->input('fatherSurname'),
            'motherSurname' => $request->input('motherSurname'),

            'address' => $request->input('address'),
            'telephone' => $request->input('telephone'),
            'email' => $request->input('email'),

            'person_id' => $request->input('person_id'),

        ], $filterNullValues);

        $object->update($Data);
        $object = ContactInfo::with(["person"])->find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transporte/public/api/contactInfo/{id}",
     *     summary="Delete a contactInfo",
     *     tags={"ContactInfo"},
     *     description="Delete a contactInfo by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the contactInfo to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ContactInfo deleted successfully",
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
        $contactInfo = ContactInfo::find($id);
        if (!$contactInfo) {
            return response()->json(['message' => 'ContactInfo not found'], 422);
        }
        $contactInfo->delete();
    }
}
