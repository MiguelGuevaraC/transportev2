<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceFormActionRequest\IndexMaintenanceFormActionRequest;
use App\Http\Resources\MaintenanceFormActionResource;
use App\Models\MaintenanceFormAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenanceFormActionController extends Controller
{
    public function index(IndexMaintenanceFormActionRequest $request)
    {
        $q = MaintenanceFormAction::query()->with(['groupMenu', 'typeofUser']);

        return $this->getFilteredResults(
            $q,
            $request,
            MaintenanceFormAction::filters,
            MaintenanceFormAction::sorts,
            MaintenanceFormActionResource::class
        );
    }

    public function show($id)
    {
        $m = MaintenanceFormAction::with(['groupMenu', 'typeofUser'])->find($id);
        if (! $m) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
        }

        return new MaintenanceFormActionResource($m);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'            => 'required|string|max:191',
            'group_menu_id'   => 'required|integer|exists:group_menus,id',
            'typeof_user_id'  => 'required|integer|exists:typeof_Users,id',
            'allowed'         => 'required|boolean',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m = MaintenanceFormAction::create($v->validated());

        return new MaintenanceFormActionResource($m->load(['groupMenu', 'typeofUser']));
    }

    public function update(Request $request, $id)
    {
        $m = MaintenanceFormAction::find($id);
        if (! $m) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
        }
        $v = Validator::make($request->all(), [
            'name'            => 'sometimes|string|max:191',
            'group_menu_id'   => 'sometimes|integer|exists:group_menus,id',
            'typeof_user_id'  => 'sometimes|integer|exists:typeof_Users,id',
            'allowed'         => 'sometimes|boolean',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m->update($v->validated());

        return new MaintenanceFormActionResource($m->fresh(['groupMenu', 'typeofUser']));
    }

    public function destroy($id)
    {
        $m = MaintenanceFormAction::find($id);
        if (! $m) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
        }
        $m->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
