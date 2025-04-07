<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class AccessController extends Controller
{
    /**
     * Get all Access
     * @OA\Get (
     *     path="/transportedev/public/api/access",
     *     tags={"Access"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active access",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/access?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/access?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transportedev/public/api/access"),
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
        // Obtén la lista de permisos con paginación simple
        $permissions = Permission::where('state', 1)
        ->orderBy('name','desc')->simplePaginate();

        // Construye la estructura de la respuesta
        $response = [

            'total' => $permissions->total(),
            'data' => $permissions->items(),
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
            'per_page' => $permissions->perPage(),
            'first_page_url' => $permissions->url(1),
            'from' => $permissions->firstItem(),
            'next_page_url' => $permissions->nextPageUrl(),
            'path' => $permissions->path(),
            'prev_page_url' => $permissions->previousPageUrl(),
            'to' => $permissions->lastItem(),

        ];

        return response()->json($response, 200);
    }

}
