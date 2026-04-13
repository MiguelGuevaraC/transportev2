<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerAbsence;
use App\Models\WorkerAttendanceEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanillaController extends Controller
{
    /**
     * Registro o actualización diaria de asistencia (ingreso/salida; compatible con huella vía source=biometric).
     */
    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'worker_id'           => 'required|integer|exists:workers,id,deleted_at,NULL',
            'attendance_date'     => 'required|date',
            'checked_in_at'       => 'nullable|date',
            'checked_out_at'      => 'nullable|date',
            'source'              => 'nullable|string|in:manual,biometric,app',
            'biometric_device_id' => 'nullable|string|max:191',
            'notes'               => 'nullable|string|max:2000',
        ]);

        $data['source'] = $data['source'] ?? 'manual';

        $event = WorkerAttendanceEvent::updateOrCreate(
            [
                'worker_id'       => $data['worker_id'],
                'attendance_date' => Carbon::parse($data['attendance_date'])->toDateString(),
            ],
            [
                'checked_in_at'       => isset($data['checked_in_at']) ? Carbon::parse($data['checked_in_at']) : null,
                'checked_out_at'      => isset($data['checked_out_at']) ? Carbon::parse($data['checked_out_at']) : null,
                'source'              => $data['source'],
                'biometric_device_id' => $data['biometric_device_id'] ?? null,
                'notes'               => $data['notes'] ?? null,
            ]
        );

        return response()->json($event->load('worker.person'), 200);
    }

    /**
     * Falta justificada (J) o injustificada para calendario de planilla.
     */
    public function storeAbsence(Request $request)
    {
        $data = $request->validate([
            'worker_id'     => 'required|integer|exists:workers,id,deleted_at,NULL',
            'absence_date'  => 'required|date',
            'absence_type'  => 'required|string|in:FALTA_J,FALTA_INJUSTIFICADA,OTRO',
            'reason'        => 'nullable|string|max:2000',
        ]);

        $absence = WorkerAbsence::create([
            'worker_id'     => $data['worker_id'],
            'absence_date'  => Carbon::parse($data['absence_date'])->toDateString(),
            'absence_type'  => $data['absence_type'],
            'reason'        => $data['reason'] ?? null,
            'user_id'       => Auth::id(),
        ]);

        return response()->json($absence->load('worker.person'), 201);
    }

    /**
     * Datos para calendario: asistencias y faltas en rango (opcional por trabajador o sucursal).
     */
    public function calendar(Request $request)
    {
        $request->validate([
            'from'            => 'required|date',
            'to'              => 'required|date|after_or_equal:from',
            'worker_id'       => 'nullable|integer|exists:workers,id,deleted_at,NULL',
            'branchOffice_id' => 'nullable|integer|exists:branch_offices,id,deleted_at,NULL',
        ]);

        if (! $request->filled('worker_id') && ! $request->filled('branchOffice_id')) {
            return response()->json(['error' => 'Debe enviar worker_id o branchOffice_id.'], 422);
        }

        $from = Carbon::parse($request->input('from'))->startOfDay();
        $to   = Carbon::parse($request->input('to'))->endOfDay();

        $workerQuery = Worker::query()->with('person');
        if ($request->filled('branchOffice_id')) {
            $workerQuery->where('branchOffice_id', $request->input('branchOffice_id'));
        }
        if ($request->filled('worker_id')) {
            $workerQuery->where('id', $request->input('worker_id'));
        }
        $workers = $workerQuery->get(['id', 'code', 'branchOffice_id', 'person_id']);

        $attQuery = WorkerAttendanceEvent::query()
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()]);
        $absQuery = WorkerAbsence::query()
            ->whereBetween('absence_date', [$from->toDateString(), $to->toDateString()]);

        if ($request->filled('worker_id')) {
            $attQuery->where('worker_id', $request->input('worker_id'));
            $absQuery->where('worker_id', $request->input('worker_id'));
        } elseif ($request->filled('branchOffice_id')) {
            $ids = $workers->pluck('id')->all();
            $attQuery->whereIn('worker_id', $ids);
            $absQuery->whereIn('worker_id', $ids);
        }

        $attendances = $attQuery->get();
        $absences    = $absQuery->get();

        $days = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $d = $cursor->toDateString();
            $days[$d] = [];
            $cursor->addDay();
        }

        foreach ($workers as $w) {
            foreach (array_keys($days) as $d) {
                $days[$d][$w->id] = [
                    'worker_id'   => $w->id,
                    'worker_code' => $w->code,
                    'person'      => $w->person,
                    'attendance'  => null,
                    'absence'     => null,
                ];
            }
        }

        foreach ($attendances as $a) {
            $d = $a->attendance_date instanceof Carbon ? $a->attendance_date->toDateString() : (string) $a->attendance_date;
            if (isset($days[$d][$a->worker_id])) {
                $days[$d][$a->worker_id]['attendance'] = [
                    'checked_in_at'       => $a->checked_in_at,
                    'checked_out_at'      => $a->checked_out_at,
                    'source'              => $a->source,
                    'biometric_device_id' => $a->biometric_device_id,
                ];
            }
        }

        foreach ($absences as $a) {
            $d = $a->absence_date instanceof Carbon ? $a->absence_date->toDateString() : (string) $a->absence_date;
            if (isset($days[$d][$a->worker_id])) {
                $days[$d][$a->worker_id]['absence'] = [
                    'absence_type' => $a->absence_type,
                    'reason'       => $a->reason,
                ];
            }
        }

        $flat = [];
        foreach ($days as $date => $byWorker) {
            $flat[] = [
                'date'    => $date,
                'workers' => array_values($byWorker),
            ];
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'days' => $flat,
        ]);
    }
}
