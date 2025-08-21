<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptionMenuRequest\StoreGroupMenuRequest;
use App\Models\GroupMenu;
use App\Services\GroupMenuService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class GroupMenuController extends Controller
{
    protected $groupMenuService;
    public function __construct(GroupMenuService $groupMenuService)
    {
        $this->groupMenuService = $groupMenuService;
    }

    public function store(StoreGroupMenuRequest $request)
    {
        $result = $this->groupMenuService->storeOrUpdateByName($request->validated());

        // Si el servicio devuelve null, mando error
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear o actualizar el grupo.',
                'data' => $result
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['created']
                ? 'Grupo creado correctamente'
                : 'Grupo actualizado correctamente',
            'data' => $result
        ]);
    }



    public function destroy(int $id)
    {
        $groupMenu = GroupMenu::find($id);

        if (!$groupMenu) {
            return response()->json([
                'success' => false,
                'message' => 'El grupo de menú no existe.'
            ], 404);
        }

        // 🚫 Verificar si el grupo tiene grupos asociados (FK en permissions)
        if (Permission::where('groupMenu_id', $groupMenu->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el grupo tiene permisos asociados.'
            ], 422);
        }

        // ✅ Eliminar si no tiene roles ni grupos asociados
        if (!$groupMenu->delete()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el grupo de menú.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grupo de menú eliminado correctamente.'
        ]);
    }






    public function list(Request $request)
    {
        $name = $request->input('name');
        $groupId = $request->input('group_id');
        $action = $request->input('action');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = GroupMenu::with([

        ]);

        if ($name) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
        }
        if ($groupId) {
            $query->where('groupMenu_id', $groupId);
        }
        if ($action) {
            $query->whereRaw('LOWER(action) LIKE ?', ['%' . strtolower($action) . '%']);
        }

        $query->orderBy('created_at', 'asc');

        /** @var \Illuminate\Pagination\LengthAwarePaginator $groupMenus */
        $groupMenus = $query->paginate($perPage, ['*'], 'page', $page);

        // Transformar los items de la colección
        $data = $groupMenus->getCollection()->map(function ($option) {
            return [
                'id' => $option->id,
                'name' => $option->name,
                'icon' => $option->icon ?? 'dot',
            ];
        });

        $groupMenus->setCollection($data);

        return response()->json($groupMenus);
    }
}
