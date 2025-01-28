<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cargos;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CargoController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validator = validator()->make($request->all(), [
            'reception_id' => 'required|integer|exists:receptions,id',
            'cargos' => 'required|array', // Validar que "cargos" es un array
            'cargos.*.description' => 'nullable|string', // Cada cargo puede tener una descripción
            'cargos.*.file' => 'required|file|mimes:pdf,jpg,png,doc,docx|max:2048', // Validar cada archivo
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Buscar la recepción
        $reception = Reception::find($request->input('reception_id'));
        if (!$reception) {
            return response()->json(['message' => 'Recepción no encontrada'], 404);
        }

        // Array para almacenar los cargos creados
        $createdCargos = [];

        // Iterar sobre los cargos y procesar archivos
        foreach ($request->cargos as $cargo) {
            // Verificar que el archivo esté presente y sea válido
            if (isset($cargo['file']) && $cargo['file'] instanceof \Illuminate\Http\UploadedFile) {
                $photoFile = $cargo['file'];
            } else {
                continue; // Saltar a la siguiente iteración si no hay archivo válido
            }

            // Descripción predeterminada si no se proporciona
            $description = $cargo['description'] ?? '-';

            // Crear un directorio para los archivos de la recepción
            $receptionDirectory = 'public/cargos/' . $reception->id;
            $filePath = $photoFile->store($receptionDirectory); // Guardar el archivo en el directorio
            $photoUrl = Storage::url($filePath); // Obtener la URL del archivo

            // Crear la entrada en la base de datos para cada cargo
            $newCargo = Cargos::create([
                'reception_id' => $reception->id,
                'description' => $description,
                'pathFile' => $photoUrl,
            ]);

            // Agregar el cargo creado al array
            $createdCargos[] = $newCargo;
        }

        // Retornar los cargos creados
        return response()->json([
            'data' => $createdCargos,
        ], 200);
    }
    public function destroy($id)
    {
        // Buscar el cargo por su ID
        $cargo = Cargos::find($id);

        // Verificar si el cargo existe
        if (!$cargo) {
            return response()->json(['message' => 'Cargo no encontrado'], 404);
        }

        // Verificar si el archivo existe en el almacenamiento y eliminarlo
        if ($cargo->pathFile) {
            $filePath = str_replace('/storage', 'public', $cargo->pathFile); // Convertir URL en la ruta correcta
            Storage::delete($filePath); // Eliminar el archivo del almacenamiento
        }

        // Eliminar el cargo de la base de datos
        $cargo->delete();

        // Retornar respuesta exitosa
        return response()->json(['message' => 'Cargo eliminado con éxito'], 200);
    }
    public function indexByReception(Request $request, $reception_id)
    {
        // Verificar que la recepción existe
        $reception = Reception::find($reception_id);
        if (!$reception) {
            return response()->json(['message' => 'Recepción no encontrada'], 404);
        }

        // Definir la cantidad de elementos por página (por defecto 10, o lo que se envíe en la petición)
        $perPage = $request->get('per_page', 10);

        // Obtener los cargos asociados a la recepción con paginación
        $cargos = Cargos::where('reception_id', $reception_id)->paginate($perPage);

        // Retornar la estructura personalizada para la paginación
        return response()->json(
            [
                'total' => $cargos->total(),
                'data' => $cargos->items(),
                'current_page' => $cargos->currentPage(),
                'last_page' => $cargos->lastPage(),
                'per_page' => $cargos->perPage(),
                'pagination' => $perPage, // Nuevo campo para tamaño de paginación
                'first_page_url' => $cargos->url(1),
                'from' => $cargos->firstItem(),
                'next_page_url' => $cargos->nextPageUrl(),
                'path' => $cargos->path(),
                'prev_page_url' => $cargos->previousPageUrl(),
                'to' => $cargos->lastItem(),

            ], 200);
    }

}
