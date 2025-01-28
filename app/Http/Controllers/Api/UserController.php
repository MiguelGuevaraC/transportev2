<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\GroupMenu;
use App\Models\Person;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    /**
     * Get all Users
     * @OA\Get (
     *     path="/transporte/public/api/user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active Useres",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Option_Menu")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/user?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/user?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/user"),
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
         $branch_office_id = $request->input('branch_office_id');
         $perPage = $request->input('per_page', 15); // Número de elementos por página (por defecto 15)
         $page = $request->input('page', 1); // Página actual (por defecto 1)
     
         if ($branch_office_id && is_numeric($branch_office_id)) {
             $branchOffice = BranchOffice::find($branch_office_id);
             if (!$branchOffice) {
                 return response()->json([
                     "message" => "Branch Office Not Found",
                 ], 404);
             }
         } else {
             $branch_office_id = auth()->user()->worker->branchOffice_id;
             $branchOffice = BranchOffice::find($branch_office_id);
         }
     
         // Paginación dinámica con `per_page` y `page`
         $list = User::with(['box', 'worker', 'worker.person', 'typeofUser'])
             ->where('state', 1)
             ->where('id', '!=', 4)
             ->paginate($perPage, ['*'], 'page', $page);
     
         return response()->json([
             'total' => $list->total(),
             'data' => $list->items(),
             'current_page' => $list->currentPage(),
             'last_page' => $list->lastPage(),
             'per_page' => $list->perPage(),
             'first_page_url' => $list->url(1),
             'from' => $list->firstItem(),
             'next_page_url' => $list->nextPageUrl(),
             'path' => $list->path(),
             'prev_page_url' => $list->previousPageUrl(),
             'to' => $list->lastItem(),
         ], 200);
     }
     

    /**
     * @OA\Post(
     *     path="/transporte/public/api/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     description="Authenticate user and generate access token",
     * security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"username", "password", "branchOffice_id"},
     *             @OA\Property(property="username", type="string", example="administrador"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="branchOffice_id", type="integer", example=1, description="ID of the branch office")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *              @OA\Property(property="token", type="string", description="token del usuario"),
     *             @OA\Property(
     *             property="user",
     *             type="object",
     *             description="User",
     *             ref="#/components/schemas/User"
     *              ),
     *          @OA\Property(
     *          property="menu",
     *          type="array",
     *              @OA\Items(
     *              type="object",
     *               description="Menú"
     *              )
     *),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="User not found or password incorrect",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message")
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

    public function login(Request $request)
    {
        $request->validate([
            "username" => "required",
            "password" => "required",
            "branchOffice_id" => 'required|exists:branch_offices,id',
        ]);

        $user = User::where("username", $request->username)->where("state", 1)->first();
        // $user = User::where("username", $request->username)->where("id", 1)->first();

        $bitacora = Bitacora::create([
            'user_id' => null, // ID del usuario que realiza la acción (usuario que intenta loguearse)
            'record_id' => null, // El ID del usuario afectado (el mismo que intenta loguearse)
            'action' => 'LOGIN', // Acción realizada
            'table_name' => 'users', // Tabla afectada
            'data' => json_encode([ // Guardar los datos como JSON
                'username' => $request->username,
                'branchOffice_id' => $request->branchOffice_id,

            ]),
            'description' => 'Inicio de sesión', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]); 

        if (!$user) {
            $bitacora->action = 'Login Fallido';
            $bitacora->description = 'Usuario No Encontrado';
            $bitacora->save();
            return response()->json([
                "error" => "Usuario No Encontrado",
                // "error" => "SISTEMA EN MANTENIMIENTO",
            ], 422);
        }

        if ($user->worker->branchOffice_id != $request->input('branchOffice_id')) {
            $bitacora->action = 'Login Fallido';
            $bitacora->description = 'Usuario No Registrador en Esta Sucursal';
            $bitacora->save();
            return response()->json([
                "error" => "Usuario No Registrador en Esta Sucursal",
            ], 422);
        }

        if (Hash::check($request->password, $user->password)) {
            // Autenticar al usuario

            Auth::login($user);

            $token = $user->createToken('auth_token', ['expires' => now()->addHour()])->plainTextToken;

            $user->makeHidden('password');
            $bitacora->user_id = $user->id;

            $bitacora->record_id = $user->id;
            $bitacora->action = 'Login Exitoso';
            $bitacora->description = 'Se logró Ingresar';
            $bitacora->save();
            // -------------------------------------------------
            return response()->json([
                'token' => $token,
                'user' => User::with(['worker', 'box', 'worker.person', 'worker.area', 'worker.branchOffice', 'typeofUser'])->find($user->id),
                'menu' => $this->obtenerMenu(),
            ]);
        } else {
            $bitacora->action = 'Login Fallido';
            $bitacora->description = 'Password no correcta';
            $bitacora->save();
            return response()->json([
                "error" => "Password Not Correct",
            ], 422);
        }
    }

    /**
     * Show the specified Users
     * @OA\Get (
     *     path="/transporte/public/api/user/{id}",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the User",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     )
     * )
     */

    public function show(int $id)
    {

        $object = User::find($id);
        if ($object) {
            return User::with(['box', 'worker', 'worker.person', 'typeofUser'])->find($object->id);
        }
        return response()->json(
            ['message' => 'User not found'], 404
        );

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/authenticate",
     *     summary="Get Profile user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     description="Get user",
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *             property="user",
     *             type="object",
     *             description="User",
     *             ref="#/components/schemas/User"
     *              ),
     *          @OA\Property(
     *          property="menu",
     *          type="array",
     *              @OA\Items(
     *              type="object",
     *               description="Menú"
     *              )
     *),
     *         )
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

    public function authenticate()
    {
        try {

            $userAuth = auth()->user();

            return response()->json([
                'user' => User::with(['worker', 'box', 'worker.person', 'worker.area', 'worker.branchOffice', 'typeofUser'])->find($userAuth->id),
                'menu' => $this->obtenerMenu(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error interno del servidor: " . $e,
            ], 500);
        }
    }

    // public function obtenerMenu()
    // {
    //     $Grupos = GroupMenu::orderBy('created_at', 'asc')->get();

    //     $User = User::find(Auth::id());
    //     $typeOfUser = Role::find($User->tipousuario_id);
    //     $Permissions = $typeOfUser == null ? [] : $typeOfUser->permissions;
    //     // $Permissions = DB::select('CALL permmisosPorRol(?)', array($User->tipousuario_id));

    //     $permis = [];
    //     foreach ($Permissions as $Permissio) {
    //         $permis[] = $Permissio->Permiso;
    //     }

    //     $optionsByGroup = [];

    //     foreach ($Grupos as $grupo) {
    //         $Permisos = $Permissions::where('group_menu_id', $grupo->id)->get();

    //         $options = [];

    //         foreach ($Permisos as $item) {
    //             if (in_array($item->name, $permis)) {
    //                 $options[] = [
    //                     'id' => $item->id,
    //                     'name' => $item->name,
    //                     'route' => $item->route,
    //                     'icon' => $item->icon,
    //                 ];
    //             }
    //         }

    //         $group_menu = [
    //             'group_name' => $grupo->name,
    //             'group_menu' => $grupo->icon,
    //             'options' => $options,
    //         ];

    //         $optionsByGroup[] = $group_menu;

    //     }

    //     return $optionsByGroup;
    // }

    // public function obtenerMenu()
    // {
    //     $Grupos = GroupMenu::whereNull('groupMenu_id')->orderBy('created_at', 'asc')->get();

    //     $User = User::find(Auth::id());
    //     $typeOfUser = Role::find($User->typeofUser_id);
    //     $Permissions = $typeOfUser == null ? [] : $typeOfUser->permissions;

    //     $permis = [];
    //     foreach ($Permissions as $Permissio) {
    //         $permis[] = $Permissio->name;
    //     }

    //     $optionsByGroup = [];

    //     foreach ($Grupos as $grupo) {
    //         $group_menu = [
    //             'groupName' => $grupo->name,
    //             'groupcon' => $grupo->icon,
    //             'options' => [],
    //         ];

    //         // Obtener subgrupos del grupo padre y ordenar por fecha de creación
    //         $subgrupos = GroupMenu::where('groupMenu_id', $grupo->id)
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         foreach ($subgrupos as $subgrupo) {
    //             $subgroupOptions = Permission::where('groupMenu_id', $subgrupo->id)
    //                 ->whereIn('name', $permis)
    //                 ->orderBy('created_at', 'asc')
    //                 ->get();

    //             $subOptions = [];

    //             foreach ($subgroupOptions as $option) {
    //                 $subOptions[] = [
    //                     'id' => $option->id,
    //                     'name' => $option->name,
    //                     'route' => $option->route,
    //                     'icon' => $option->icon,
    //                 ];
    //             }

    //             $group_menu['options'][] = [
    //                 'groupName' => $subgrupo->name,
    //                 'groupcon' => $subgrupo->icon,
    //                 'options' => $subOptions,
    //             ];

    //         }

    //         // Obtener opciones directas del grupo padre y ordenar por fecha de creación
    //         $directOptions = Permission::where('groupMenu_id', $grupo->id)
    //             ->whereIn('name', $permis)
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         foreach ($directOptions as $option) {
    //             $group_menu['options'][] = [
    //                 'id' => $option->id,
    //                 'name' => $option->name,
    //                 'route' => $option->route,
    //                 'icon' => $option->icon,
    //             ];
    //         }

    //         $optionsByGroup[] = $group_menu;

    //     }

    //     return $optionsByGroup;
    // }

    public function obtenerMenu()
    {
        $Grupos = GroupMenu::whereNull('groupMenu_id')->orderBy('created_at', 'asc')->get();

        $User = User::find(Auth::id());
        $typeOfUser = Role::find($User->typeofUser_id);
        $Permissions = $typeOfUser == null ? [] : $typeOfUser->permissions;

        $permis = [];
        foreach ($Permissions as $Permissio) {
            $permis[] = $Permissio->name;
        }

        $optionsByGroup = [];

        foreach ($Grupos as $grupo) {
            $group_menu = [
                'id' => $grupo->id,
                'title' => $grupo->name,
                'name' => strtolower(str_replace(' ', '_', $grupo->name)),
                'parent' => true,
                'icon' => $grupo->icon,
                'link' => strtolower(str_replace(' ', '_', $grupo->name)),
                'child' => [],
            ];

            // Obtener subgrupos del grupo padre y ordenar por fecha de creación
            $subgrupos = GroupMenu::where('groupMenu_id', $grupo->id)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($subgrupos as $subgrupo) {
                $subgroupOptions = Permission::where('groupMenu_id', $subgrupo->id)
                    ->whereIn('name', $permis)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $subOptions = [];

                foreach ($subgroupOptions as $option) {
                    $subOptions[] = [
                        'id' => $option->id,
                        'title' => $option->name,
                        'name' => strtolower(str_replace(' ', '_', $option->name)),
                        'link' => $option->route,
                        'icon' => 'dot',
                    ];
                }

                $group_menu['child'][] = [
                    'id' => $subgrupo->id,
                    'title' => $subgrupo->name,
                    'name' => strtolower(str_replace(' ', '_', $subgrupo->name)),
                    'link' => strtolower(str_replace(' ', '_', $subgrupo->name)),
                    'icon' => 'dot',
                    'child' => $subOptions,
                ];

            }

            // Obtener opciones directas del grupo padre y ordenar por fecha de creación
            $directOptions = Permission::where('groupMenu_id', $grupo->id)
                ->whereIn('name', $permis)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($directOptions as $option) {
                $group_menu['child'][] = [
                    'id' => $option->id,
                    'title' => $option->name,
                    'name' => strtolower(str_replace(' ', '_', $option->name)),
                    'link' => $option->route,
                    'icon' => 'dot',
                ];
            }

            $optionsByGroup[] = $group_menu;
        }

        return response()->json($optionsByGroup);
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout",
     *     description="Log out user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="An error occurred while trying to log out. Please try again later.")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        try {
            if (Auth::check()) {
                // $accessToken = auth()->user()->currentAccessToken();

                // if ($accessToken) {
                //     $accessToken->delete();
                // }
                $accessToken = auth()->user()->currentAccessToken();

                if ($accessToken) {
                    // Establecer una nueva fecha de expiración (por ejemplo, 30 días a partir de ahora)
                    $accessToken->expires_at = Carbon::now()->addDays(30);
                    $accessToken->save();
                }
            } else {
                return response()->json([
                    "msg" => "Unable to logout. User is not authenticated.",
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            // Captura la excepción de la base de datos (por ejemplo, si hay un problema al eliminar el token)
            return response()->json([
                "msg" => "An error occurred while logging out.",
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/searchByDni/{dni}",
     *     tags={"Search"},
     *     summary="Search information by DNI",
     *     description="Search information about a person by their DNI number.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="dni",
     *         in="path",
     *         required=true,
     *         description="DNI number of the person to search",
     *         @OA\Schema(type="string")
     *     ),
     *       @OA\Response(
     *         response=200,
     *         description="Information found successfully.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="code", type="integer", example=0),
     *                 @OA\Property(property="dni", type="string", example="string"),
     *                 @OA\Property(property="apepat", type="string", example="string"),
     *                 @OA\Property(property="apemat", type="string", example="string"),
     *                 @OA\Property(property="apcas", type="string", example=""),
     *                 @OA\Property(property="nombres", type="string", example="string"),
     *                 @OA\Property(property="fecnac", type="string", format="date"),
     *                 @OA\Property(property="ubigeo", type="integer")
     *             )
     *         )
     *     ),
     *           @OA\Response(
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

    public function searchByDni($dni)
    {

        $respuesta = array();
        $client = new Client();
        try {
            $res = $client->get('http://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php?' . 'dni=' . $dni . '&fe=N&token=qusEj_w7aHEpX');

            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = $res->getBody()->getContents();
                $respuesta = json_decode($response_data);
                return response()->json([
                    $respuesta,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
                    "msg" => "Server Error",
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 0,
                "msg" => "Server Error: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/searchByRuc/{ruc}",
     *     tags={"Search"},
     *     summary="Search information by RUC",
     *     description="Search information about a person by their RUC number.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="ruc",
     *         in="path",
     *         required=true,
     *         description="RUC number of the person to search",
     *         @OA\Schema(type="string")
     *     ),
     *          @OA\Response(
     *         response=200,
     *         description="Information found successfully.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="code", type="integer", example=0),
     *                 @OA\Property(property="RUC", type="string", example="string"),
     *                 @OA\Property(property="RazonSocial", type="string", example="string"),
     *                 @OA\Property(property="Direccion", type="string", example="string"),
     *                 @OA\Property(property="Tipo", type="string"),
     *                 @OA\Property(property="Inscripcion", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */

    public function searchByRuc($ruc)
    {
        $respuesta = array();

        $client = new Client([
            'verify' => false,
        ]);
        $res = $client->get('https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php?fe=N&token=qusEj_w7aHEpX&' . 'ruc=' . $ruc);
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        } else {
            return response()->json([
                "status" => 0,
                "msg" => "Server error",
            ], 500);
        }
        return response()->json([
            $respuesta,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/user",
     *     summary="Store a new user",
     *     tags={"User"},
     *     description="Create a new user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="john_doe", description="Username"),
     *             @OA\Property(property="password", type="string", example="password123", description="Password"),
     *             @OA\Property(property="worker_id", type="integer", example=1, description="Worker ID"),
     *             @OA\Property(property="box_id", type="integer", example=1, description="Box ID"),
     *  @OA\Property(property="typeof_user_id", type="integer", example=1, description="typeof_user ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1, description="User ID"),
     *             @OA\Property(property="username", type="string", example="john_doe", description="Username"),
     *             @OA\Property(property="worker_id", type="integer", example=1, description="Worker ID"),
     *             @OA\Property(property="box_id", type="integer", example=1, description="Box ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some fields are required.")
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

    public function store(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'username' => [
                'required',
                'string',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'password' => 'required',
            'worker_id' => 'required|numeric|exists:workers,id',
            'typeof_user_id' => 'required|numeric|exists:typeof_Users,id',
            'box_id' => 'nullable|numeric|exists:boxes,id',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $hashedPassword = Hash::make($request->input('password'));

        $data = [

            'username' => $request->input('username') ?? null,
            'password' => $hashedPassword ?? null,

            'box_id' => $request->input('box_id') ?? null,
            'worker_id' => $request->input('worker_id') ?? null,
            'typeofUser_id' => $request->input('typeof_user_id') ?? null,
        ];

        $object = User::create($data);
        return User::with(['box', 'worker', 'worker.person', 'typeofUser'])->find($object->id);

    }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/user/{id}",
     *     summary="Update an existing user",
     *     tags={"User"},
     *     description="Update an existing user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="john_doe", description="Username"),
     *             @OA\Property(property="password", type="string", example="password123", description="Password"),
     *             @OA\Property(property="worker_id", type="integer", example=1, description="Worker ID"),
     *             @OA\Property(property="box_id", type="integer", example=1, description="Box ID"),
     *  @OA\Property(property="typeof_user_id", type="integer", example=1, description="typeof_user ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
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
        $client = User::find($id);
        if (!$client) {
            return response()->json(['message' => 'User not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'username' => [
                'required',
                Rule::unique('users')->ignore($id)->whereNull('deleted_at'),
            ],
            'password' => 'required',
            'worker_id' => 'required|numeric|exists:workers,id',
            'typeof_user_id' => 'required|numeric|exists:typeof_Users,id',
            'box_id' => 'nullable|numeric|exists:boxes,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $hashedPassword = Hash::make($request->input('password'));

        $data = [

            'username' => $request->input('username') ?? null,
            'password' => $hashedPassword ?? null,

            'box_id' => $request->input('box_id') ?? null,
            'worker_id' => $request->input('worker_id') ?? null,
            'typeofUser_id' => $request->input('typeof_user_id') ?? null,
        ];

        $object = $client->update($data);
        return User::with(['box', 'worker', 'worker.person', 'typeofUser'])->find($client->id);
    }

    /**
     * Remove the specified User
     * @OA\Delete (
     *     path="/transporte/public/api/user/{id}",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the User",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     ),

     * )
     *
     */

    public function destroy($id)
    {
        $client = User::find($id);
        if (!$client) {
            return response()->json(['message' => 'User not found'], 422);
        }

        if ($client->state == 0) {
            return response()->json(['message' => 'User not found'], 422);
        }

        $client->state = 0;
        $client->save();

        $user = User::with(['box', 'worker', 'worker.person', 'typeofUser'])->find($client->id);
        return response()->json($user, 200);
    }

}
