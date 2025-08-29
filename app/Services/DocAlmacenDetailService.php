<?php

namespace App\Services;

use App\Models\DocAlmacenDetail;
use Illuminate\Support\Facades\Log;
use Exception;

class DocAlmacenDetailService
{
    public function getDocAlmacenDetailById(int $id): ?DocAlmacenDetail
    {
        try {
            return DocAlmacenDetail::find($id);
        } catch (Exception $e) {
            Log::error("Error al obtener detalle de DocAlmacen con ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    public function createDocAlmacenDetail(array $data): ?DocAlmacenDetail
    {
        try {
            return DocAlmacenDetail::create($data);
        } catch (Exception $e) {
            Log::error("Error al crear detalle de DocAlmacen: " . $e->getMessage(), ['data' => $data]);
            return null;
        }
    }

    public function updateDocAlmacenDetail(DocAlmacenDetail $instance, array $data): ?DocAlmacenDetail
    {
        try {
            $filteredData = array_intersect_key($data, $instance->getAttributes());
            $instance->update($filteredData);
            return $instance;
        } catch (Exception $e) {
            Log::error("Error al actualizar detalle de DocAlmacen ID {$instance->id}: " . $e->getMessage(), ['data' => $data]);
            return null;
        }
    }

    public function destroyById($id): bool
    {
        try {
            return DocAlmacenDetail::find($id)?->delete() ?? false;
        } catch (Exception $e) {
            Log::error("Error al eliminar detalle de DocAlmacen con ID {$id}: " . $e->getMessage());
            return false;
        }
    }
}
