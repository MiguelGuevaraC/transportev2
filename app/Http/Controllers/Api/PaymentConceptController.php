<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentConcept;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentConceptController extends Controller
{
    /**
     * Get all PaymentConcepts
     * @OA\Get (
     *     path="/transportedev/public/api/paymentConcept",
     *     tags={"PaymentConcept"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"Ingreso", "Egreso"},
     *             example="Ingreso"
     *         ),
     *         description="Filtrar por tipo de concepto de pago: Ingreso o Egreso"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active PaymentConcepts",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PaymentConcept")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/paymentConcept?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/paymentConcept?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transportedev/public/api/paymentConcept"),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="prev_page_url", type="string", example="null"),
     *             @OA\Property(property="to", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message", type="string", example="Unauthenticated"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $type = $request->input('type');

        if ($type && !in_array($type, ['Ingreso', 'Egreso'])) {
            return response()->json(['error' => 'Solo se aceptan valores como: Ingreso, Egreso'], 422);
        }

        $list = PaymentConcept::orderBy('id', 'desc')
            ->where('state', 1)
            ->when($type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->whereNotIn('id', [1, 2, 3, 4,11])
            ->simplePaginate(30);

        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/paymentConcept",
     *     summary="Store a new paymentConcept",
     *     tags={"PaymentConcept"},
     *     description="Create a new paymentConcept",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="PaymentConcept data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Gasto Luz", description="Name PaymentConcept"),
     *         @OA\Property(property="type", type="string", example="Egreso", description="type"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="PaymentConcept created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PaymentConcept")
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
            'name' => [
                'required',
                'string',
                Rule::unique('payment_concepts')->whereNull('deleted_at'),
            ],
            'type' => 'required|in:Ingreso,Egreso',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'type' => $request->input('type'),
        ];

        $object = PaymentConcept::create($data);
        $object = PaymentConcept::find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/paymentConcept/{id}",
     *     summary="Get a paymentConcept by ID",
     *     tags={"PaymentConcept"},
     *     description="Retrieve a paymentConcept by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the paymentConcept to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PaymentConcept found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/PaymentConcept"
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
        $object = PaymentConcept::whereNotIn('id', [1, 2])->find($id);
        if (!$object) {
            return response()->json(['message' => 'PaymentConcept not found'], 422);
        }
        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/paymentConcept/{id}",
     *     summary="Update an existing PaymentConcept",
     *     tags={"PaymentConcept"},
     *     description="Update an existing PaymentConcept",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the PaymentConcept to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="PaymentConcept data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Gasto Luz", description="Name PaymentConcept"),
     *         @OA\Property(property="type", type="string", example="Egreso", description="type"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="PaymentConcept updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PaymentConcept")
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
        $object = PaymentConcept::find($id);
        if (!$object) {
            return response()->json(['message' => 'PaymentConcept not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                'string',
                Rule::unique('payment_concepts')->ignore($id)->whereNull('deleted_at'),
            ],
            'type' => 'required|in:Ingreso,Egreso',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $routeData = array_filter([
            'name' => $request->input('name'),
            'type' => $request->input('type'),

        ], $filterNullValues);

        $object->update($routeData);

        $object = PaymentConcept::find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/paymentConcept/{id}",
     *     summary="Delete a PaymentConcept",
     *     tags={"PaymentConcept"},
     *     description="Delete a PaymentConcept by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the PaymentConcept to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PaymentConcept deleted successfully",
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
        $object = PaymentConcept::find($id);
        if (!$object) {
            return response()->json(['message' => 'PaymentConcept not found'], 422);
        }
        if (in_array($id, [1, 2, 3,4])) {
            return response()->json(['message' => 'This Payment Concept cannot be deleted'], 422);
        }

        $object->state = 0;
        $object->save();
    }
}
