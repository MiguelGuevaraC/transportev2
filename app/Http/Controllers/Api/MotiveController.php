<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motive;
use Illuminate\Http\Request;

class MotiveController extends Controller
{
    /**
     * Get all motives with pagination
     * @OA\Get (
     *      path="/transporte/public/api/motive",
     *      tags={"Motive"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="List of active motives",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Motive")),
     *              @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/motive?page=1"),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/motive?page=2"),
     *              @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/motive"),
     *              @OA\Property(property="per_page", type="integer", example=15),
     *              @OA\Property(property="prev_page_url", type="string", example="null"),
     *              @OA\Property(property="to", type="integer", example=15)
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        // Establecemos valores predeterminados para `page` y `per_page`
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);

        // Asegúrate de usar `simplePaginate` para obtener un paginador
        $motive = Motive::orderBy('name', 'asc')->paginate($perPage, ['*'], 'page', $page);

        // Retornar los datos en un formato estructurado
        return response()->json([
            'total' => $motive->total(),
            'data' => $motive->items(),
            'current_page' => $motive->currentPage(),
            'last_page' => $motive->lastPage(),
            'per_page' => $motive->perPage(),
            'first_page_url' => $motive->url(1),
            'from' => $motive->firstItem(),
            'next_page_url' => $motive->nextPageUrl(),
            'path' => $motive->path(),
            'prev_page_url' => $motive->previousPageUrl(),
            'to' => $motive->lastItem(),
        ]);
    }




     /**
     * Display the specified motive.
     * @OA\Get (
     *      path="/transporte/public/api/motive/{id}",
     *      tags={"Motive"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Motive ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Motive found",
     *          @OA\JsonContent(ref="#/components/schemas/Motive")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Motive not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Motive not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function show(int $id)
    {
        $motive = Motive::find($id);

        if ($motive === null) {
            return response()->json(['message' => 'Motivo no encontrado'], 404);
        }

        return response()->json($motive);
    }
}
