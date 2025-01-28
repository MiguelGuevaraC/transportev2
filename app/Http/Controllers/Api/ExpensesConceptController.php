<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpensesConcept;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpensesConceptController extends Controller
{

    /**
     * Get all Expneses Concept
     * @OA\Get (
     *     path="/transportev2/public/api/expensesConcept",
     *     tags={"ExpensesConcept"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active Expneses Concept",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ExpensesConcept")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transportev2/public/api/expensesConcept?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transportev2/public/api/expensesConcept?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transportev2/public/api/expensesConcept"),
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

     public function index()
     {
 
         $list = ExpensesConcept::orderBy('id', 'desc')
         ->whereNotIn('id', [21])
         ->where('state', 1)->simplePaginate(30);
         return response()->json($list, 200);
     }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/expensesConcept",
     *     summary="Store a new expenses Concept",
     *     tags={"ExpensesConcept"},
     *     description="Create a new expenses Concept",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="ExpensesConcept data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="concepto 1", description="Name ExpensesConcept"),
     *         @OA\Property(property="type", type="string", example="G-RUTA/DATOS", description="G-RUTA/DATOS"),
     *         @OA\Property(property="typeConcept", type="string", example="Ingreso/Egreso", description="Ingres/Egreso"),

     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="ExpensesConcept created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ExpensesConcept")
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
                Rule::unique('expenses_concepts')->whereNull('deleted_at'),
            ],
            'type' => 'required|in:DATOS,G-RUTA',
            'typeConcept' => 'required|in:Ingreso,Egreso',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'typeConcept' => $request->input('typeConcept'),

        ];

        $object = ExpensesConcept::create($data);
        $object = ExpensesConcept::find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/expensesConcept/{id}",
     *     summary="Get a expenses Concept by ID",
     *     tags={"ExpensesConcept"},
     *     description="Retrieve a expenses Concept by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the expenses Concept to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ExpensesConcept found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/ExpensesConcept"
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
        $object = ExpensesConcept::find($id);
        if (!$object) {
            return response()->json(['message' => 'ExpensesConcept not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/expensesConcept/{id}",
     *     summary="Update an existing ExpensesConcept",
     *     tags={"ExpensesConcept"},
     *     description="Update an existing ExpensesConcept",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ExpensesConcept to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="concepto 1", description="Name ExpensesConcept"),
     *         @OA\Property(property="type", type="string", example="G-RUTA/DATOS", description="G-RUTA/DATOS"),
     *         @OA\Property(property="typeConcept", type="string", example="Ingreso/Egreso", description="Ingres/Egreso"),

     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ExpensesConcept")
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
        $object = ExpensesConcept::find($id);
        if (!$object) {
            return response()->json(['message' => 'Expenses Concept not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                'string',
                Rule::unique('expenses_concepts')->ignore($id)->whereNull('deleted_at'),
            ],
            'type' => 'required|in:DATOS,G-RUTA',
            'typeConcept' => 'required|in:Ingreso,Egreso',
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
            'typeConcept' => $request->input('typeConcept'),

        ], $filterNullValues);

        $object->update($routeData);

        $object = ExpensesConcept::find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/expensesConcept/{id}",
     *     summary="Delete a ExpensesConcept",
     *     tags={"ExpensesConcept"},
     *     description="Delete a ExpensesConcept by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ExpensesConcept to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ExpensesConcept deleted successfully",
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
        $object = ExpensesConcept::find($id);
        if (!$object) {
            return response()->json(['message' => 'ExpensesConcept not found'], 422);
        }
        $object->state = 0;
        $object->save();
    }
}
