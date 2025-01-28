<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoxController extends Controller
{
    /**
     * Get all Boxes
     * @OA\Get (
     *     path="/transporte/public/api/box",
     *     tags={"Box"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de cajas sin asignarse a algun usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Box")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/box?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/box?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/box"),
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

    public function indexNotAssigned()
    {
        $assignedBoxIds = User::whereNotNull('box_id')->pluck('box_id');
        $list = Box::with(['branchOffice'])
            ->whereNotIn('id', $assignedBoxIds)->simplePaginate();

        return response()->json($list, 200);
    }
    /**
     * Get all Boxes
     * @OA\Get (
     *     path="/transporte/public/api/boxAll",
     *     tags={"Box"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active Boxes",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Box")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/box?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/box?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/box"),
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
        $user = Auth()->user();
        $user_id = $user->id ?? '';

        // Obtener el branch_office_id del request o del usuario autenticado
        $branch_office_id = $request->input('branch_office_id');

        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = $user->worker->branchOffice_id ?? null;
            if (!$branch_office_id) {
                return response()->json([
                    "message" => "Branch Office Not Found for the user",
                ], 404);
            }
        }

        // Construir la consulta base
        $list = Box::with(['branchOffice'])
            ->where('state', 1)
            ->orderBy('id', 'desc');

        // Si el usuario no es admin, filtrar por su branchOffice_id
        //  if ($user_id != 1) {
        //      $list->where('branchOffice_id', $branch_office_id);
        //  }

        // Paginar los resultados
        $paginatedList = $list->simplePaginate();

        return response()->json($paginatedList, 200);
    }

/**
 *

 *
 * @OA\Get(
 *     path="/transporte/public/api/boxByBranch/{id}",
 *     summary="Get all box by Branch Office",
 *     tags={"Box"},
 *     description="Show all box by Branch Office",
 *     security={{"bearerAuth":{}}},
 *           @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Branch Office",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of box by Branch Office",
 *        @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Box")
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

    public function getBoxByBrandId($idBranchOffice)
    {
        $assignedBoxIds = User::whereNotNull('box_id')->pluck('box_id');

        $user = Auth()->user();
        $user_id = $user->id ?? '';

        $list = Box::with(['branchOffice'])
            ->orderBy('id', 'desc')
            ->where('state', 1);

        // Solo filtra por sucursal si el usuario no es el admin (user_id != 1)
        // if ($user_id != 1) {
        $list->where('branchOffice_id', $idBranchOffice);
        // }

        $list->whereNotIn('id', $assignedBoxIds);

        // Ejecutar la consulta
        $result = $list->get();

        return response()->json($result, 200);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/box",
     *     summary="Store a new box",
     *     tags={"Box"},
     *     description="Create a new box",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Box data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Caja Miguel", description="Name Box"),
     *         @OA\Property(property="branchOffice_id", type="integer", example=1, description="id Branch Office"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Box created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Box")
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
                Rule::unique('boxes')->whereNull('deleted_at'),
            ],
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'serie' => [
                'required',
                'string',
                'size:2', // Debe tener exactamente 2 caracteres
                Rule::notIn(['10', '20', '30','40']), // Excluye las series específicas
                Rule::unique('boxes')->whereNull('deleted_at'), // Asegura unicidad
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'serie' => $request->input('serie')?? null,
            'branchOffice_id' => $request->input('branchOffice_id'),
        ];

        $object = Box::create($data);
        $object = Box::with(['branchOffice'])->find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/box/{id}",
     *     summary="Get a box by ID",
     *     tags={"Box"},
     *     description="Retrieve a box by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the box to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/Box"
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
        $object = Box::with(['branchOffice'])->find($id);
        if (!$object) {
            return response()->json(['message' => 'Box not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/box/{id}",
     *     summary="Update an existing Box",
     *     tags={"Box"},
     *     description="Update an existing Box",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Box to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Caja Alvaro", description="Name Box"),
     *         @OA\Property(property="branchOffice_id", type="integer", example=1, description="id Branch Office"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Box")
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
        $object = Box::find($id);
        if (!$object) {
            return response()->json(['message' => 'Box not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                'string',
                Rule::unique('boxes')->ignore($id)->whereNull('deleted_at'),
            ],
            'serie' => [
                'required',
                'string',
                'size:2', // Debe tener exactamente 2 caracteres
                Rule::notIn(['10', '20', '30','40']), // Excluye las series específicas
                Rule::unique('boxes')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $routeData = [
            'name' => $request->input('name'),
            'serie' => $request->input('serie') ?? null,

        ];

        $object->update($routeData);

        $object = Box::with(['branchOffice'])->find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transporte/public/api/box/{id}",
     *     summary="Delete a Box",
     *     tags={"Box"},
     *     description="Delete a Box by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Box to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Box deleted successfully",
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
        $object = Box::find($id);
        if (!$object) {
            return response()->json(['message' => 'Box not found'], 422);
        }
        $object->state = 0;
        $object->save();
    }
}
