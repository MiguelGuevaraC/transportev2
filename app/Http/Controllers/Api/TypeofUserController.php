<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TypeofUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transporte/public/api/typeofUser",
     *     summary="Get all typeofUser",
     *     tags={"TypeofUser"},
     *     description="Show all typeofUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of typeofUser",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TypeOf_User")
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
        // $list = Role::with(['permissions',"permissions.groupMenu"])->orderBy('id', 'desc')->get();
        $list = Role::whereNotIn('id', [1])
        ->orderBy('name','asc')->get();
        return response()->json($list, 200);
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
     *     path="/transporte/public/api/typeofUser",
     *     summary="Store a new typeofUser",
     *     tags={"TypeofUser"},
     *     description="Create a new typeofUser",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="TypeOf_User data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Cajero", description="Name Type of User"),

     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="TypeOf_User created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TypeOf_User")
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
            'name' => 'required|unique:typeof_Users,name',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
            'guard_name' => 'web',
        ];

        $object = Role::create($data);
        // $object = Role::with(['permissions',"permissions.groupMenu"])->find($object->id);
        $object = Role::find($object->id);
        return response()->json($object, 200);

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/typeofUser/{id}",
     *     summary="Get a typeofUser by ID",
     *     tags={"TypeofUser"},
     *     description="Retrieve a typeofUser by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the typeofUser to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TypeofUser found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/TypeOf_User"
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
        // $object = Role::with(['permissions',"permissions.groupMenu"])->find($id);
        $object = Role::find($id);
        if (!$object) {
            return response()->json(['message' => 'Type of User not found'], 422);
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
     * @OA\Put(
     *     path="/transporte/public/api/typeofUser/{id}",
     *     summary="Update an existing type of User",
     *     tags={"TypeofUser"},
     *     description="Update an existing type of User",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the type of User to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Cajero2", description="Name Type of User"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TypeOf_User")
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
        $object = Role::find($id);
        if (!$object) {
            return response()->json(['message' => 'TypeOfUser not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'name' => 'required|unique:typeof_Users,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $routeData = array_filter([
            'name' => $request->input('name'),

        ], $filterNullValues);

        $object->update($routeData);

        // $object = Role::with(['permissions',"permissions.groupMenu"])->find($id);
        $object = Role::find($object->id);

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transporte/public/api/typeofUser/{id}",
     *     summary="Delete a TypeofUser",
     *     tags={"TypeofUser"},
     *     description="Delete a TypeofUser by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the TypeofUser to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TypeOf_User deleted successfully",
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
        $object = Role::find($id);
        if (!$object) {
            return response()->json(['message' => 'TypeOf_User not found'], 422);
        }

        if (in_array($id, [1, 2, 3, 4,9])) {
            return response()->json(['message' => 'This Type User cannot be deleted'], 422);
        }

        $object->delete();
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/setAccess/{typeUserId}",
 *     tags={"TypeofUser"},
 *     summary="Set access to type of User",
 *     description="Set access permissions for a specific type of user.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="typeUserId",
 *         in="path",
 *         required=true,
 *         description="ID of the type of user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Options menu in array format",
 *         @OA\JsonContent(
 *             required={"optionsMenu"},
 *             @OA\Property(
 *                 property="optionsMenu",
 *                 type="array",
 *                 @OA\Items(type="integer"),
 *                 example={1, 2, 3},
 *                 description="List of permission IDs to assign"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permissions assigned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Permissions assigned successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Validation error."),
 *             @OA\Property(property="errors", type="object", description="Object containing validation errors")
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

    public function setAccess(Request $request)
    {
        $typeUserId = $request->route('typeUserId');

        $validator = validator()->make(array_merge($request->all(), ['typeUserId' => $typeUserId]), [
            'typeUserId' => 'required|exists:typeof_Users,id',
            'optionsMenu' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $role = Role::find($typeUserId);
        $role->syncPermissions($request->input('optionsMenu'));

        $permissions = Role::find($role->id)->permissions()
            ->with('groupMenu')
            ->get();

        return response()->json([
            "Option_Menu" => $permissions,
        ]);
    }

    /**
     * Assign permissions to a specific type of user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/transporte/public/api/getAccess/{typeUserId}",
     *     tags={"TypeofUser"},
     *     summary="Get access to type of User",
     *     description="Get access to a specific user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="typeUserId",
     *          in="path",
     *         required=true,
     *         description="ID of the type of user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions get successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Option_Menu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Informative message", description="Informative message"),
     *             @OA\Property(property="errors", type="object", description="Object with validation errors")
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

    public function getAccess(Request $request, $id)
    {
        $object = Role::find($id);
        if (!$object) {
            return response()->json(['message' => 'Type of User not found'], 422);
        }

        $permissions = Role::find($id)->permissions()
        ->with('groupMenu')
        ->orderBy('name')
        ->get();
        return response()->json([
            "Option_Menu" => $permissions,
        ]);
    }
}
