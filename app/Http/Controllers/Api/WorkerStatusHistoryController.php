<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkerStatusHistoryController extends Controller
{
    public function index($workerId)
    {
        $worker = Worker::find($workerId);
        if (! $worker) {
            return response()->json(['message' => 'Trabajador no encontrado'], 404);
        }
        $rows = WorkerStatusHistory::where('worker_id', $workerId)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function store(Request $request, $workerId)
    {
        $worker = Worker::find($workerId);
        if (! $worker) {
            return response()->json(['message' => 'Trabajador no encontrado'], 404);
        }
        $v = Validator::make($request->all(), [
            'action'          => 'required|string|in:disable,enable',
            'reason'          => 'required_if:action,disable|nullable|string|max:2000',
            'effective_date'  => 'required|date',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $action = $request->input('action');
        WorkerStatusHistory::create([
            'worker_id'      => $worker->id,
            'action'         => $action,
            'reason'         => $request->input('reason'),
            'effective_date' => $request->input('effective_date'),
            'user_id'        => Auth::id(),
        ]);

        $worker->state = $action === 'enable';
        $worker->save();

        return response()->json([
            'message' => 'Historial registrado y estado del trabajador actualizado.',
            'worker'  => $worker->fresh(['person', 'area', 'branchOffice']),
        ]);
    }
}
