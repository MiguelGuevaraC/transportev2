<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcontract;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubContractController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transportev2/public/api/subcontract",
     *     summary="Get all subcontract",
     *     tags={"Subcontract"},
     *     description="Show all subcontract",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of subcontract",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Subcontract")
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

    public function index(Request $request)
    {
        // Crear una consulta base
        $query = Subcontract::query();

        // Aplicar filtros dinámicos solo si los valores no están vacíos

        if (!empty($request->input('typeofDocument'))) {
            $query->where('typeofDocument', $request->input('typeofDocument'));
        }
        if (!empty($request->input('documentNumber'))) {
            $query->where('documentNumber', 'like', '%' . $request->input('documentNumber') . '%');
        }
        if (!empty($request->input('comercialName'))) {
            $query->where('comercialName', 'like', '%' . $request->input('comercialName') . '%');
        }
        if (!empty($request->input('representativePersonName'))) {
            $query->where('representativePersonName', 'like', '%' . $request->input('representativePersonName') . '%');
        }
        if (!empty($request->input('telephone'))) {
            $query->where('telephone', 'like', '%' . $request->input('telephone') . '%');
        }

        // Configurar la paginación
        $perPage = !empty($request->input('per_page')) ? (int) $request->input('per_page') : 15; // Valor predeterminado: 10
        $page = !empty($request->input('page')) ? (int) $request->input('page') : 1; // Valor predeterminado: 1

        // Obtener los resultados paginados
        $subcontracts = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        // Devolver los datos en el formato solicitado
        return response()->json([
            'total' => $subcontracts->total(),
            'data' => $subcontracts->items(),
            'current_page' => $subcontracts->currentPage(),
            'last_page' => $subcontracts->lastPage(),
            'per_page' => $subcontracts->perPage(),
            'pagination' => $perPage,
            'first_page_url' => $subcontracts->url(1),
            'from' => $subcontracts->firstItem(),
            'next_page_url' => $subcontracts->nextPageUrl(),
            'path' => $subcontracts->path(),
            'prev_page_url' => $subcontracts->previousPageUrl(),
            'to' => $subcontracts->lastItem(),
        ], 200);
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
     *     path="/transportes/public/api/subcontract",
     *     summary="Create a subcontract",
     *     tags={"Subcontract"},
     *     description="Create a new subcontract",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"typeofDocument", "documentNumber", "address", "comercialName", "representativePersonDni", "representativePersonName", "telephone"},
     *             @OA\Property(property="typeofDocument", type="string", example="DNI"),
     *             @OA\Property(property="documentNumber", type="string", example="12345678"),
     *             @OA\Property(property="address", type="string", example="Av. Siempre Viva 123"),
     *             @OA\Property(property="comercialName", type="string", example="Empresa Comercial S.A."),
     *             @OA\Property(property="representativePersonDni", type="string", example="87654321"),
     *             @OA\Property(property="representativePersonName", type="string", example="Juan Pérez"),
     *             @OA\Property(property="telephone", type="string", example="987654321"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Subcontract")),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="422", description="Unprocessable Entity")
     * )
     */

    public function store(Request $request)
    {
        // Reglas de validación

        $validator = validator()->make($request->all(), [

            'typeofDocument' => 'required|string|in:DNI,RUC',
            'documentNumber' => [
                'required',
                Rule::unique('subcontracts')->whereNull('deleted_at'),
            ],
            'address' => 'nullable|string|max:255',
            'comercialName' => 'nullable|string|max:255',
            'representativePersonDni' => 'nullable|string|max:8',
            'representativePersonName' => 'nullable|string|max:255',
            'telephone' => 'nullable|string',
        ]);

        // Verificar si la validación falla
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Crear un nuevo registro
        $subcontract = Subcontract::create($request->only([
            'typeofDocument',
            'documentNumber',
            'address',
            'comercialName',
            'representativePersonDni',
            'representativePersonName',
            'telephone',
        ]));

        // Devolver respuesta exitosa
        return response()->json($subcontract, 201);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/subcontract/{id}",
     *     summary="Get a subcontract by ID",
     *     tags={"Subcontract"},
     *     description="Retrieve a subcontract by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the subcontract to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subcontract found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/Subcontract"
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
        $object = Subcontract::find($id);
        if (!$object) {
            return response()->json(['message' => 'Sub Contract not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportes/public/api/subcontract/{id}",
     *     summary="Update a subcontract",
     *     tags={"Subcontract"},
     *     description="Update an existing subcontract",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the subcontract to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"typeofDocument", "documentNumber", "address", "comercialName", "representativePersonDni", "representativePersonName", "telephone"},
     *             @OA\Property(property="typeofDocument", type="string", example="DNI"),
     *             @OA\Property(property="documentNumber", type="string", example="12345678"),
     *             @OA\Property(property="address", type="string", example="Av. Siempre Viva 123"),
     *             @OA\Property(property="comercialName", type="string", example="Empresa Comercial S.A."),
     *             @OA\Property(property="representativePersonDni", type="string", example="87654321"),
     *             @OA\Property(property="representativePersonName", type="string", example="Juan Pérez"),
     *             @OA\Property(property="telephone", type="string", example="987654321"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Subcontract")),
     *     @OA\Response(response="404", description="Subcontract not found"),
     *     @OA\Response(response="422", description="Unprocessable Entity")
     * )
     */

    public function update(Request $request, $id)
    {
        // Buscar el registro existente
        $subcontract = Subcontract::find($id);

        if (!$subcontract) {
            return response()->json(['error' => 'Subcontract not found'], 404);
        }

        // Reglas de validación
        $validator = validator()->make($request->all(), [
            'typeofDocument' => 'required|string|in:DNI,RUC',
            'documentNumber' => [
                'required',
                Rule::unique('subcontracts')->ignore($subcontract->id)->whereNull('deleted_at'),
            ],
            'address' => 'nullable|string|max:255',
            'comercialName' => 'nullable|string|max:255',
            'representativePersonDni' => 'nullable|string|max:8',
            'representativePersonName' => 'nullable|string|max:255',
            'telephone' => 'nullable|string',
        ]);

        // Verificar si la validación falla
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Actualizar el registro existente
        $subcontract->update($request->only([
            'typeofDocument',
            'documentNumber',
            'address',
            'comercialName',
            'representativePersonDni',
            'representativePersonName',
            'telephone',
        ]));
        $subcontract = Subcontract::find($id);

        // Devolver respuesta exitosa
        return response()->json($subcontract, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/subcontract/{id}",
     *     summary="Delete a Subcontract",
     *     tags={"Subcontract"},
     *     description="Delete a Subcontract by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Subcontract to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sub Contract deleted successfully",
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
        $object = Subcontract::find($id);
        if (!$object) {
            return response()->json(['message' => 'Sub Contract not found'], 422);
        }
        if ($object->guides()->count() > 0) {
            return response()->json(['message' => 'Sub Contratista Tiene Guias Asignadas'], 422);
        }

        $object->delete();
    }
}
