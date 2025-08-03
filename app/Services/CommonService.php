<?php
namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommonService
{
    public function store_photo(array $data, object $object, string $name_folder)
    {

        $ruta = "https://develop.garzasoft.com/transportedev/public";

        // Verificar si se han subido imágenes y si es un array
        if (isset($data['imagesave']) && is_array($data['imagesave'])) {
            $imagePaths = []; // Un array para almacenar las rutas de las imágenes subidas
            $i = 1;
            foreach ($data['imagesave'] as $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile) {
                    $timestamp = now()->format('Ymd_His');
                    $extension = $image->getClientOriginalExtension();
                    $fileName = "{$object->id}_{$i}_{$timestamp}.{$extension}";
                    $filePath = $image->storeAs($name_folder, $fileName, 'public');
                    $i++;
                    // Guardar la ruta completa de la imagen
                    $imagePaths[] = $ruta . Storage::url($filePath);
                }
            }

            // Verificar que $imagePaths no esté vacío y que sea un array
            if (!empty($imagePaths) && is_array($imagePaths)) {
                // Convertir el array de rutas a una cadena separada por comas
                $imagePathsString = implode(',', $imagePaths);

                // Asegurarse de que la columna en la base de datos sea una cadena
                $object->update(['images' => $imagePathsString]); // Guardar las rutas como una cadena separada por comas
            }
        }
    }

    public function update_photo(array $data, object $object, string $name_folder): string
    {
        $ruta = "https://develop.garzasoft.com/transportedev/public";

        // Verificar si existe una ruta de imágenes anteriores y eliminarlas
        if (!empty($object->images)) {
            // Obtener las rutas anteriores separadas por comas
            $oldPaths = explode(',', $object->images);

            // Eliminar las imágenes previas
            foreach ($oldPaths as $oldPath) {
                $oldPath = str_replace($ruta . '/storage/', '', $oldPath); // Limpiar la ruta

                // Verificar si el archivo existe y eliminarlo
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        // Verificar si se proporcionaron nuevas imágenes
        $imagePaths = []; // Un array para almacenar las rutas de las imágenes subidas
        if (isset($data['imagesave']) && is_array($data['imagesave'])) {
            $i = 1; // Inicializar el contador de imágenes
            foreach ($data['imagesave'] as $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile) {
                    $timestamp = now()->format('Ymd_His');
                    $extension = $image->getClientOriginalExtension();
                    $fileName = "{$object->id}_{$i}_{$timestamp}.{$extension}";
                    $filePath = $image->storeAs($name_folder, $fileName, 'public');
                    $i++;

                    // Guardar la ruta completa de la imagen
                    $imagePaths[] = $ruta . Storage::url($filePath);
                }
            }
        }

        // Si se subieron nuevas imágenes, actualizar las rutas
        if (!empty($imagePaths)) {
            // Convertir el array de rutas a una cadena separada por comas
            $imagePathsString = implode(',', $imagePaths);

            // Devolver la cadena de rutas
            return $imagePathsString;
        }

        // Si no se subieron nuevas imágenes, devolver lo anterior
        return $object->images;
    }


    public function algoritmoanexos($document)
    {
        $pattern = '/([A-Za-z0-9]+)-([0-9]{1,8})/';
        $documentsArray = explode(',', $document);
        $formattedDocuments = []; // Inicializar arreglo para documentos formateados

        if (strpos($document, '-') !== false && $document != '' && $document != null) {
            // Dividir la cadena en un array de documentos
            foreach ($documentsArray as $doc) {
                // Eliminar espacios adicionales al inicio y al final
                $input = trim($doc);

                // Verificar si el documento contiene un guion (-)
                if (strpos($input, '-') !== false) {
                    // Separar en base al guion
                    list($beforeDash, $afterDash) = explode('-', $input, 2);

                    // Tomar los últimos 4 caracteres antes del guion como serie (sin espacios)
                    // Si la serie tiene menos de 4 caracteres, agregar ceros a la izquierda
                    $serie = strtoupper(substr(str_replace(' ', '', $beforeDash), -4));
                    $serie = str_pad($serie, 4, '0', STR_PAD_LEFT); // Completar la serie con ceros si es necesario

                    // Limpiar el correlativo y tomar solo los últimos 8 dígitos
                    $correlativo = preg_replace('/\D/', '', $afterDash);        // Eliminar caracteres no numéricos
                    $correlativo = substr($correlativo, -8);                    // Tomar solo los últimos 8 dígitos
                    $correlativo = str_pad($correlativo, 8, '0', STR_PAD_LEFT); // Completar con ceros si es necesario

                    // Combinar la serie y el correlativo en el formato requerido
                    $formattedDocuments[] = $serie . '-' . $correlativo;
                }

            }

            // Reconstruir la cadena de documentos formateados separados por comas
            $document = implode(',', $formattedDocuments);
        }

        return $document;
    }


    public function logActivity(
        string $action,
        string $tableName,
        int $recordId,
        string $description,
        array $oldData = [],
        array $newData = []
    ): Bitacora {
        return Bitacora::create([
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'description' => $description,
            'data_old' => json_encode(['old' => $oldData]),
            'data_new' => json_encode(['new' => $newData]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'user_id' => Auth::id() ?? null, // Obtiene el ID del usuario autenticado
        ]);
    }

}
