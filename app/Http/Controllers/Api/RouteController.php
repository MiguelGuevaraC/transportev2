<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Place;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transporte/public/api/routes",
     *     summary="Get all routes",
     *     tags={"Routes"},
     *     description="Show all routes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of routes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Route")
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
    public function index(Request $request)
    {
        // Obtener los valores de 'per_page' y 'page' desde la solicitud, con valores predeterminados
        $perPage = $request->get('per_page', 15); // Valor predeterminado de 15 si no se envía
        $page = $request->get('page', 1); // Valor predeterminado de 1 si no se envía
        $routeName = $request->get('cadena', ''); // Valor predeterminado de 1 si no se envía
        if ($perPage == -1) {
            // Obtener todas las rutas con las relaciones necesarias, sin paginación
            $query = Route::with(['placeStart', 'placeEnd','routes'])
                ->where('routes.state', 1)
                ->selectRaw("routes.*, CONCAT(placeStart.name, ' - ', placeEnd.name) AS route_name")

                ->join('places AS placeStart', 'routes.placeStart_id', '=', 'placeStart.id')
                ->join('places AS placeEnd', 'routes.placeEnd_id', '=', 'placeEnd.id');


                    if (!empty($routeName)) {
                        $query = $query->whereRaw("CONCAT(placeStart.name, ' - ', placeEnd.name) LIKE ?", ['%' . $routeName. '%']);
                       // $query->where('route_name', 'like', '%' . $routeName . '%');

                    }



                $lista= $query->orderBy('route_name', 'asc')
                ->get(); // Usamos get() para obtener todos los resultados

            // Crear la respuesta manualmente para colecciones
            return response()->json([
                'total' => $lista->count(), // Total de elementos
                'data' => $lista, // Todos los datos
                'current_page' => 1, // Siempre página 1
                'last_page' => 1, // Solo hay una página
                'per_page' => $lista->count(), // Todos los elementos se devuelven como "por página"
                'pagination' => null, // Sin paginación
                'first_page_url' => null,
                'from' => $lista->isNotEmpty() ? 1 : null,
                'next_page_url' => null,
                'path' => $request->url(),
                'prev_page_url' => null,
                'to' => $lista->isNotEmpty() ? $lista->count() : null,
            ], 200);
        } else {
            // Aplicar paginación normalmente
            $query= Route::with(['placeStart', 'placeEnd','routes'])
                ->where('routes.state', 1)
                ->selectRaw("routes.*, CONCAT(placeStart.name, ' - ', placeEnd.name) AS route_name")
                ->join('places AS placeStart', 'routes.placeStart_id', '=', 'placeStart.id')
                ->join('places AS placeEnd', 'routes.placeEnd_id', '=', 'placeEnd.id');


                if (!empty($routeName)) {
                    $query = $query->whereRaw("CONCAT(placeStart.name, ' - ', placeEnd.name) LIKE ?", ['%' . $routeName. '%']);
                   // $query->where('route_name', 'like', '%' . $routeName . '%');

                }


                $lista=$query->orderBy('route_name', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Crear la estructura para la paginación
            return response()->json([
                'total' => $lista->total(),
                'data' => $lista->items(),
                'current_page' => $lista->currentPage(),
                'last_page' => $lista->lastPage(),
                'per_page' => $lista->perPage(),
                'pagination' => $perPage,
                'first_page_url' => $lista->url(1),
                'from' => $lista->firstItem(),
                'next_page_url' => $lista->nextPageUrl(),
                'path' => $lista->path(),
                'prev_page_url' => $lista->previousPageUrl(),
                'to' => $lista->lastItem(),
            ], 200);
        }
    }




    /**
     * @OA\Get(
     *     path="/transporte/public/api/routesFather",
     *     summary="Get all parent routes",
     *     tags={"Routes"},
     *     description="MUESTRA LOS QUE TIENEN GRUPO",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of parent routes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Route")
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
    public function indexRoutesFather()
    {

        $list = Route::with(['routes', 'placeStart', 'placeEnd'])
            ->where('state', 1)
            ->whereHas('routes') // Filtra las rutas que tienen subrutas (al menos una)
            ->orderBy('id', 'desc')
            ->simplePaginate(15);

        return response()->json($list, 200);
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/routes/{id}",
 *     summary="Get a specific route by ID",
 *     tags={"Routes"},
 *     description="Show details of a specific route",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the route to retrieve",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Route details",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/Route"
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Route not found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Route not found.")
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
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['msg' => 'Route not found.'], 404);
        }
        $route = Route::with(['routes', 'placeStart', 'placeEnd', 'routeFather'])->find($id);

        return response()->json($route, 200);
    }
/**
 * @OA\Delete(
 *     path="/transporte/public/api/routes/{id}",
 *     summary="Delete a specific route by ID",
 *     tags={"Routes"},
 *     description="Delete a specific route",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the route to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Route deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Route deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Route not found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Route not found.")
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
    public function destroy($id)
    {
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['msg' => 'Route not found.'], 404);
        }

        $route->state = 0;
        $route->save();
        return response()->json(['msg' => 'Route deleted successfully.'], 200);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/routes",
 *     summary="Create a new route",
 *     tags={"Routes"},
 *     description="Store a new route in the system and assign a parent route to specified routes.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"placeStart_id", "placeEnd_id", "routes"},
 *                 @OA\Property(
 *                     property="placeStart_id",
 *                     type="integer",
 *                     description="ID of the starting place",
 *                     example=1
 *                 ),
 *                 @OA\Property(
 *                     property="placeEnd_id",
 *                     type="integer",
 *                     description="ID of the ending place",
 *                     example=2
 *                 ),

 *                 @OA\Property(
 *                     property="routes",
 *                     type="array",
 *                     @OA\Items(
 *                         type="integer",
 *                         description="Array of route IDs to be assigned as child routes",
 *                         example=4
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Route created successfully.",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/Route"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error.",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="The placeStart_id field is required.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthenticated.")
 *         )
 *     ),
 * )
 */

    public function store(Request $request)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'placeStart_id' => 'required|exists:places,id',
            'placeEnd_id' => 'required|exists:places,id',
            'routes' => 'nullable|array', // Asegúrate de que 'routes' sea un array
            'routes.*' => 'exists:routes,id', // Cada elemento en el array debe ser un ID válido
        ]);

        $validator->after(function ($validator) use ($request) {
            $placeStartId = $request->input('placeStart_id');
            $placeEndId = $request->input('placeEnd_id');

            // if ($placeStartId == $placeEndId) {
            //     $validator->errors()->add('placeEnd_id', 'Las rutas deben ser diferentes.');
            // }

            // Verificar si ya existe una ruta con los mismos lugares de inicio y fin en una sola consulta
            $routeExists = Route::where('placeStart_id', $placeStartId)
                ->where('placeEnd_id', $placeEndId)
                ->exists();

            if ($routeExists) {
                $validator->errors()->add('placeEnd_id', 'Ya existe una ruta con estos lugares de inicio y fin.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Crear la nueva ruta
        $start = Place::find($request->input('placeStart_id'));
        $end = Place::find($request->input('placeEnd_id'));

        $data = [
            'placeEnd' => $end->name ?? '',
            'placeStart' => $start->name ?? '',
            'placeStart_id' => $request->input('placeStart_id') ?? null,
            'placeEnd_id' => $request->input('placeEnd_id') ?? null,
        ];

        $route = Route::create($data);

        $routeIds = $request->input('routes', []);
        Route::whereIn('id', $routeIds)->update(['routeFather_id' => $route->id]);

        $route = Route::with(['routes', 'placeStart', 'placeEnd', 'routeFather'])->find($route->id);

        return response()->json($route, 200);
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/routeStore",
 *     summary="Create a new route",
 *     tags={"Routes"},
 *     description="Store a new route in the system, specifying the starting and ending places.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"placeStart_id", "placeEnd_id"},
 *                 @OA\Property(
 *                     property="placeStart_id",
 *                     type="integer",
 *                     description="ID of the starting place",
 *                     example=1
 *                 ),
 *                 @OA\Property(
 *                     property="placeEnd_id",
 *                     type="integer",
 *                     description="ID of the ending place",
 *                     example=2
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Route created successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="placeStart_id", type="integer", example=1),
 *             @OA\Property(property="placeEnd_id", type="integer", example=2),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-21T14:15:22Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-21T14:15:22Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error.",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="The placeStart_id field is required.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Places not found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Places not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

    public function storeRoute(Request $request)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'placeStart_id' => 'required|exists:places,id',
            'placeEnd_id' => 'required|exists:places,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $start = Place::find($request->input('placeStart_id'));
        $end = Place::find($request->input('placeEnd_id'));

        if (!$start || !$end) {
            return response()->json(['message' => 'Places not found'], 404);
        }

        $existingRoute = Route::where('placeStart_id', $request->input('placeStart_id'))
            ->where('placeEnd_id', $request->input('placeEnd_id'))
            ->first();

        if ($existingRoute) {
            return response()->json([
                $existingRoute,
            ], 200);
        }

        $data = [
            'placeEnd' => $end->name ?? '',
            'placeStart' => $start->name ?? '',
            'placeStart_id' => $request->input('placeStart_id') ?? null,
            'placeEnd_id' => $request->input('placeEnd_id') ?? null,
        ];

        $route = Route::create($data);

        $routeWithRelations = Route::with(['routes', 'placeStart', 'placeEnd', 'routeFather'])
            ->find($route->id);

        return response()->json($routeWithRelations, 200);

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/searchRoute",
     *     summary="Search for an existing route",
     *     tags={"Routes"},
     *     description="Search for a route in the system based on the starting and ending places.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="placeStart_id",
     *         in="query",
     *         description="ID of the starting place",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="placeEnd_id",
     *         in="query",
     *         description="ID of the ending place",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Route found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="route",
     *                 description="Details of the existing route",
     *                 type="object",
     *                 @OA\Property(property="placeStart_id", type="integer", description="ID of the starting place"),
     *                 @OA\Property(property="placeEnd_id", type="integer", description="ID of the ending place"),
     *                 @OA\Property(property="placeStart", type="string", description="Name of the starting place"),
     *                 @OA\Property(property="placeEnd", type="string", description="Name of the ending place")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="No route found.",
     *         @OA\JsonContent(
     *             description="No content when no route is found"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The placeStart_id field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Place not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Places not found")
     *         )
     *     )
     * )
     */

    public function searchRoute(Request $request)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'placeStart_id' => 'required|exists:places,id',
            'placeEnd_id' => 'required|exists:places,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $start = Place::find($request->input('placeStart_id'));
        $end = Place::find($request->input('placeEnd_id'));

        if (!$start || !$end) {
            return response()->json(['message' => 'Places not found'], 404);
        }

        $existingRoute = Route::with(['routes', 'routeFather'])->where('placeStart_id', $request->input('placeStart_id'))
            ->where('placeEnd_id', $request->input('placeEnd_id'))
            ->first();

        if ($existingRoute) {
            return response()->json($existingRoute, 200);
        } else {

            return response()->json(null, 200);
        }
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/addSubRoute",
     *     summary="Store a new subroute with a parent route",
     *     tags={"Routes"},
     *     description="Creates a new subroute and associates it with a specified parent route. If the subroute already exists, it updates its association with the parent route.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"route_id", "subPlaceStart_id", "subPlaceEnd_id"},
     *                 @OA\Property(
     *                     property="route_id",
     *                     type="integer",
     *                     description="ID of the parent route",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="subPlaceStart_id",
     *                     type="integer",
     *                     description="ID of the starting place for the subroute",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="subPlaceEnd_id",
     *                     type="integer",
     *                     description="ID of the ending place for the subroute",
     *                     example=3
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subroute created or updated successfully and associated with the parent route.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Subroute created successfully."),
     *             @OA\Property(property="route", type="object", description="The parent route with updated subroutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parent route or places not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Parent route or places not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error message.")
     *         )
     *     )
     * )
     */

    public function addSubRoute(Request $request)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'subPlaceStart_id' => 'required|exists:places,id',
            'subPlaceEnd_id' => 'required|exists:places,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $route = Route::find($request->input('route_id'));

        $start = Place::find($request->input('subPlaceStart_id'));
        $end = Place::find($request->input('subPlaceEnd_id'));

        if (!$start || !$end) {
            return response()->json(['message' => 'Places not found'], 404);
        }

        $existingRoute = Route::where('placeStart_id', $request->input('subPlaceStart_id'))
            ->where('placeEnd_id', $request->input('subPlaceEnd_id'))
            ->first();

        if ($existingRoute) {

            $existingRoute->update(['routeFather_id' => $route->id]);
        } else {

            $data = [
                'placeEnd' => $end->name ?? '',
                'placeStart' => $start->name ?? '',
                'placeStart_id' => $request->input('subPlaceStart_id') ?? null,
                'placeEnd_id' => $request->input('subPlaceEnd_id') ?? null,
                'routeFather_id' => $route->id ?? null,
            ];
            Route::create($data);
        }

        return response()->json([Route::with(['routes', 'routeFather'])->find($route->id),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/routes/assign-parent",
     *     summary="Assign a parent route to multiple routes",
     *     tags={"Routes"},
     *     description="Assign an existing route as a parent to multiple routes.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"routes", "routeFather_id"},
     *                 @OA\Property(
     *                     property="routes",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of route IDs to be assigned a parent",
     *                     example={1, 2, 3}
     *                 ),
     *                 @OA\Property(
     *                     property="routeFather_id",
     *                     type="integer",
     *                     description="ID of the parent route",
     *                     example=3
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parent route assigned successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Parent route assigned successfully."),
     *             @OA\Property(
     *                 property="routes",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Route")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="One or more routes or Parent route not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="One or more routes or Parent route not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The routeFather_id field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function assignParentRoute(Request $request)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'routes' => 'required|array',
            'routes.*' => 'exists:routes,id',
            'routeFather_id' => 'required|exists:routes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $routeFatherId = $request->input('routeFather_id');

        $routeFather = Route::find($routeFatherId);
        if (!$routeFather) {
            return response()->json(['msg' => 'Parent route not found.'], 404);
        }

        $routeIds = $request->input('routes');
        $routeFather->routes()->update(['routeFather_id' => null]);
        Route::whereIn('id', $routeIds)->update(['routeFather_id' => $routeFatherId]);

        // Obtener las rutas actualizadas con relaciones para la respuesta
        $updatedRoutes = Route::with(['routes', 'placeStart', 'placeEnd', 'routeFather'])
            ->whereIn('id', $routeIds)
            ->get();

        return response()->json($updatedRoutes, 200);
    }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/routes/{id}",
     *     summary="Update an existing route",
     *     tags={"Routes"},
     *     description="Update an existing route in the system and assign a parent route to specified routes.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the route to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"placeStart_id", "placeEnd_id"},
     *                 @OA\Property(
     *                     property="placeStart_id",
     *                     type="integer",
     *                     description="ID of the starting place",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="placeEnd_id",
     *                     type="integer",
     *                     description="ID of the ending place",
     *                     example=2
     *                 ),

     *                 @OA\Property(
     *                     property="routes",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of route IDs to assign the parent route",
     *                     example={1, 2, 3}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Route updated successfully.",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Route"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Route not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Route not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The placeStart_id field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function update(Request $request, $id)
    {
        // Validar la solicitud
        $validator = validator()->make($request->all(), [
            'placeStart_id' => 'required|exists:places,id',
            'placeEnd_id' => 'required|exists:places,id',
            'routes' => 'nullable|array', // Asegúrate de que 'routes' sea un array
            'routes.*' => 'exists:routes,id', // Cada elemento en el array debe ser un ID válido
        ]);


        $validator->after(function ($validator) use ($request, $id) {
            $placeStartId = $request->input('placeStart_id');
            $placeEndId = $request->input('placeEnd_id');

            // if ($placeStartId == $placeEndId) {
            //     $validator->errors()->add('placeEnd_id', 'Las rutas deben ser diferentes.');
            // }

            // Verificar si ya existe una ruta con los mismos lugares de inicio y fin, excluyendo la ruta actual
            $routeExists = Route::where('placeStart_id', $placeStartId)
                ->where('placeEnd_id', $placeEndId)
                ->where('id', '!=', $id) // Excluir la ruta actual
                ->exists();

            if ($routeExists) {
                $validator->errors()->add('placeEnd_id', 'Ya existe una ruta con estos lugares de inicio y fin.');
            }
        });

        // Si la validación falla, devolver el error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Buscar la ruta existente
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['msg' => 'Route not found.'], 404);
        }

        // if ($route->routes()->exists()) {
        //     return response()->json([
        //         'message' => __('La ruta no puede ser editada porque tiene subrutas.'),
        //         'status'  => 422,
        //     ], 422);
        // }


        // Actualizar la ruta
        $start = Place::find($request->input('placeStart_id'));
        $end = Place::find($request->input('placeEnd_id'));

        $data = [
            'placeEnd' => $end->name ?? '',
            'placeStart' => $start->name ?? '',
            'placeStart_id' => $request->input('placeStart_id') ?? null,
            'placeEnd_id' => $request->input('placeEnd_id') ?? null,
        ];

        $route->update($data);

        $routeIds = $request->input('routes', []);
        if ($routeIds != []) {
            $route->routes()->update(['routeFather_id' => null]);
            Route::whereIn('id', $routeIds)->update(['routeFather_id' => $route->id]);
        }

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $id, // El ID del usuario afectado
            'action' => 'Update', // Acción realizada
            'table_name' => 'routes', // Tabla afectada
            'data' => json_encode($route),
            'description' => 'Edita Ruta', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        $route = Route::with(['routes', 'placeStart', 'placeEnd', 'routeFather'])->find($route->id);

        return response()->json($route, 200);
    }

}
