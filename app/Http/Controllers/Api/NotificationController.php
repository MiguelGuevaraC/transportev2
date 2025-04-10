<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {

        $rangoadelante = 5; // Cantidad de días hacia adelante
        $rangoatras    = 3; // Cantidad de días hacia atrás

                                                                   // Calcular el rango de fechas alrededor del día actual (3 días atrás hasta 3 días adelante)
        $fechaInicio = now()->subDays($rangoatras)->startOfDay();  // Fecha 3 días atrás
        $fechaFin    = now()->addDays($rangoadelante)->endOfDay(); // Fecha 3 días adelante

        // Obtener documentos cuya fecha de vencimiento está dentro del rango de interés
        $documentsExpiringSoon = Document::
            whereDate('dueDate', '>=', $fechaInicio)
        //->whereDate('dueDate', '<=', $fechaFin)
        //where('status', "Vencido") // Asegúrate de tener este campo para filtrar los vigentes
        //->where('state', "1")
            ->get();

        $currentDate = now()->startOfDay(); // Fecha actual normalizada a inicio del día

        if ($documentsExpiringSoon->isNotEmpty()) {

            foreach ($documentsExpiringSoon as $document) {
                $dueDate = Carbon::parse($document->dueDate)->startOfDay();

                // Calcular la diferencia en días
                $daysDifference = $currentDate->diffInDays($dueDate, false);

                // Asignar prioridad y mensaje basados en la diferencia de días
                if ($daysDifference === 0 and $document->state != 0) {
                    $priority = 'medium';
                    $message  = "El documento con número {$document->number} vence hoy.";
                } elseif ($daysDifference < 0 and $document->state != 0) {
                    $priority = 'high';
                    $message  = "El documento con número {$document->number} venció hace " . abs($daysDifference) . " días.";
                } elseif ($daysDifference < 5 and $document->state != 0) {
                    $priority = $daysDifference === 1 ? 'medium' : 'low';
                    $message  = "El documento con número {$document->number} vence en {$daysDifference} días.";
                } else {
                    // Si no está dentro del rango, elimina cualquier notificación existente
                    Notification::where('record_id', $document->id)
                        ->where('table', 'documents')
                        ->delete();
                    continue;
                }

                // Buscar notificación existente
                $notification = Notification::where('record_id', $document->id)
                    ->where('table', 'documents')
                    ->latest('created_at') // Buscar la más reciente
                    ->first();

                if ($notification) {
                    // Actualizar la notificación solo si la prioridad o mensaje han cambiado
                    if ($notification->priority !== $priority || $notification->message !== $message) {
                        $notification->update([
                            'priority' => $priority,
                            'message'  => $message,
                        ]);
                    }
                } else {
                    // Crear una nueva notificación si no existe
                    Notification::create([
                        'record_id'   => $document->id,
                        'title'       => "Notificación de vencimiento",
                        'message'     => $message,
                        'type'        => 'warning',
                        'table'       => 'documents',
                        'priority'    => $priority,
                        'dueDate'     => $document->dueDate,
                        'vehicle_id'  => $document->vehicle_id,
                        'document_id' => $document->id,
                        'created_at'  => now(),
                    ]);
                }
            }
        } else {
            // Eliminar todas las notificaciones que estén fuera del rango
            Notification::where('table', 'documents')
            // ->whereNotBetween('dueDate', [now()->subDays(5), now()->addDays(3)])
                ->delete();
        }

        // Validar parámetros de entrada
        $validated = $request->validate([
            'page'      => 'integer|min:1',                          // Página debe ser un entero mayor o igual a 1
            'per_page'  => 'integer|min:-1|max:100',                 // Tamaño de página entre -1 (sin paginación) y 100
            'title'     => 'string|nullable',                        // Filtro opcional por título
            'type'      => 'string|nullable',                        // Filtro opcional por tipo
            'priority'  => 'string|in:low,medium,high|nullable',     // Prioridad: "low", "medium" o "high"
            'startDate' => 'date|nullable',                          // Fecha de inicio opcional
            'endDate'   => 'date|nullable|after_or_equal:startDate', // Fecha de fin opcional, no menor que startDate
        ]);

        // Determinar tamaño de página
        $per_page = $validated['per_page'] ?? 10;

        // Construir consulta inicial con relaciones necesarias
        $query = Notification::with('vehicle', 'document');

        // Aplicar filtros condicionalmente
        if (! empty($validated['title'])) {
            $query->where('title', 'LIKE', '%' . $validated['title'] . '%');
        }

        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (! empty($validated['priority'])) {
            $query->where('priority', $validated['priority']);
        }

        if (! empty($validated['startDate'])) {
            $query->whereDate('created_at', '>=', $validated['startDate']);
        }

        if (! empty($validated['endDate'])) {
            $query->whereDate('created_at', '<=', $validated['endDate']);
        }

        // Ordenar resultados por fecha de creación (más reciente primero)
        $query->orderBy('created_at', 'desc');
        $per_page = -1;
        // Obtener datos y estructura uniforme
        if ($per_page == -1) {
            // Sin paginación: obtener todos los registros
            $notifications = $query->get();

            return response()->json([
                'total'        => $notifications->count(),
                'data'         => $notifications,
                'current_page' => 1,
                'last_page'    => 1,
                'per_page'     => $notifications->count(),
                'pagination'   => [
                    'size'           => -1,
                    'first_page_url' => null,
                    'next_page_url'  => null,
                    'prev_page_url'  => null,
                    'path'           => null,
                ],
                'from'         => $notifications->isEmpty() ? null : 1,
                'to'           => $notifications->isEmpty() ? null : $notifications->count(),
            ], 200);
        }

        // Con paginación: obtener datos paginados
        $notifications = $query->paginate($per_page);

        return response()->json([
            'total'        => $notifications->total(),
            'data'         => $notifications->items(),
            'current_page' => $notifications->currentPage(),
            'last_page'    => $notifications->lastPage(),
            'per_page'     => $notifications->perPage(),
            'pagination'   => [
                'size'           => $per_page,
                'first_page_url' => $notifications->url(1),
                'next_page_url'  => $notifications->nextPageUrl(),
                'prev_page_url'  => $notifications->previousPageUrl(),
                'path'           => $notifications->path(),
            ],
            'from'         => $notifications->firstItem(),
            'to'           => $notifications->lastItem(),
        ], 200);
    }

}
