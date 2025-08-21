<?php

namespace App\Http\Controllers;

use App\Http\Requests\OptionMenuRequest\StoreOptionMenuRequest;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class OptionMenuController extends Controller
{

    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function store(StoreOptionMenuRequest $request)
    {
        $result = $this->permissionService->storeOrUpdate($request->validated());

        // Si el servicio devuelve null, mando error
        if (!$result || !$result['permission']) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear o actualizar el permiso.',
                'data' => $result
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['created']
                ? 'Permiso creado correctamente'
                : 'Permiso actualizado correctamente',
            'data' => $result['permission']
        ]);
    }


    public function getAccessAll(Request $request)
    {
        $name = $request->input('name');
        $groupId = $request->input('group_id');
        $action = $request->input('action');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = Permission::with([

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

        /** @var \Illuminate\Pagination\LengthAwarePaginator $permissions */
        $permissions = $query->paginate($perPage, ['*'], 'page', $page);

        // Transformar los items de la colección
        $data = $permissions->getCollection()->map(function ($option) {
            return [
                'id' => $option->id,
                'title' => $option->name,
                'name' => strtolower(str_replace(' ', '_', $option->name)),
                'link' => $option->route,
                'action' => $option->action,
                'group' => $option->groupMenu ? $option->groupMenu->name : null,
                'icon' => $option->icon ?? 'dot',
            ];
        });

        $permissions->setCollection($data);

        return response()->json($permissions);
    }
}
