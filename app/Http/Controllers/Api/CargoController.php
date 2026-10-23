<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cargos;
use App\Models\Reception;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CargoController extends Controller
{
    public function store(Request $request)
    {
        $uploadedCargos = $request->file('cargos', []);
        foreach ((array) $uploadedCargos as $index => $uploadedCargo) {
            $file = is_array($uploadedCargo) ? ($uploadedCargo['file'] ?? null) : null;
            if ($file && $file->getError() !== UPLOAD_ERR_OK) {
                return response()->json([
                    'error' => "No se pudo subir cargos.{$index}.file. Verifique el tamaño del archivo y vuelva a intentarlo.",
                    'upload_error' => $file->getError(),
                ], 422);
            }
        }

        // Validar los datos recibidos
        $validator = validator()->make($request->all(), [
            'reception_id' => 'required|integer|exists:receptions,id',
            'cargos' => 'required|array', // Validar que "cargos" es un array
            'cargos.*.description' => 'nullable|string', // Cada cargo puede tener una descripción
            'cargos.*.file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480', // Hasta 20 MB por archivo
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Buscar la recepción
        $reception = Reception::find($request->input('reception_id'));
        if (!$reception) {
            return response()->json(['message' => 'Recepción no encontrada'], 404);
        }

        // Array para almacenar los cargos creados
        $createdCargos = [];

        // Iterar sobre los cargos y procesar archivos
        $cargoInputs = $request->input('cargos', []);
        foreach ($cargoInputs as $index => $cargo) {
            // Verificar que el archivo esté presente y sea válido
            $photoFile = $uploadedCargos[$index]['file'] ?? null;
            if ($photoFile instanceof \Illuminate\Http\UploadedFile && $photoFile->isValid()) {
                // Archivo válido.
            } else {
                return response()->json([
                    'error' => "El archivo cargos.{$index}.file es obligatorio y debe ser válido.",
                ], 422);
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
        $authorization = $this->validateCargoDeletionAccess(request());
        if ($authorization !== true) {
            return $authorization;
        }

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

    /**
     * Actualiza la clave de eliminación de cargos.
     * Solo el usuario autorizado puede cambiarla.
     */
    public function updateDeletionPassword(Request $request)
    {
        $authorization = $this->validateAuthorizedUser();
        if ($authorization !== true) {
            return $authorization;
        }

        $data = $request->validate([
            'current_password' => 'required|digits:4',
            'password' => 'required|digits:4|confirmed',
        ]);

        $setting = SystemSetting::firstOrCreate(
            ['key' => 'cargo_delete_password'],
            ['value' => Hash::make('4828')]
        );

        if (!Hash::check($data['current_password'], $setting->value)) {
            return response()->json([
                'message' => 'La clave actual no es válida.',
            ], 422);
        }

        $setting->update(['value' => Hash::make($data['password'])]);

        return response()->json([
            'message' => 'Clave de eliminación actualizada correctamente.',
        ]);
    }

    protected function validateCargoDeletionAccess(Request $request)
    {
        $authorization = $this->validateAuthorizedUser();
        if ($authorization !== true) {
            return $authorization;
        }

        $request->validate([
            'password' => 'required|digits:4',
        ]);

        $setting = SystemSetting::where('key', 'cargo_delete_password')->first();
        if (!$setting || !Hash::check($request->input('password'), $setting->value)) {
            return response()->json([
                'message' => 'La clave de eliminación no es válida.',
            ], 422);
        }

        return true;
    }

    protected function validateAuthorizedUser()
    {
        $user = Auth::user();
        $authorizedUsername = strtolower((string) env(
            'CARGO_DELETE_AUTHORIZED_USER',
            'cynthiaventuradelgado'
        ));

        $isAdministrator = $user && in_array((int) $user->typeofUser_id, [1, 2], true);
        $isAuthorizedUser = $user && strtolower((string) $user->username) === $authorizedUsername;

        if (! $user || (! $isAdministrator && ! $isAuthorizedUser)) {
            return response()->json([
                'message' => 'No tienes autorización para eliminar cargos.',
            ], 403);
        }

        return true;
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
