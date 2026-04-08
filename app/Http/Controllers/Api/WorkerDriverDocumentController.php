<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WorkerDriverDocumentController extends Controller
{
    public function index(Request $request)
    {
        $workerId = $request->query('worker_id');
        if (! $workerId) {
            return response()->json(['message' => 'Indique worker_id'], 422);
        }
        $docs = Document::with(['typeDocument'])
            ->where('worker_id', $workerId)
            ->where('state', 1)
            ->orderByDesc('id')
            ->get();

        return response()->json($docs);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'worker_id'         => 'required|integer|exists:workers,id',
            'description'       => 'required|string|max:255',
            'type_document_id'  => 'required|integer|exists:type_documents,id',
            'number'            => 'nullable|string',
            'dueDate'           => 'nullable|date',
            'pathFile'          => 'required|file|max:10240',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $worker = Worker::find($request->input('worker_id'));
        if (! $worker) {
            return response()->json(['message' => 'Trabajador no encontrado'], 404);
        }

        $file     = $request->file('pathFile');
        $dir      = 'public/workerDocuments/' . $worker->id;
        $filePath = $file->store($dir);
        $url      = Storage::url($filePath);

        $doc = Document::create([
            'pathFile'         => $url,
            'description'      => $request->input('description'),
            'type'             => $request->input('description'),
            'number'           => $request->input('number'),
            'dueDate'          => $request->input('dueDate'),
            'status'           => 'Vigente',
            'state'            => 1,
            'vehicle_id'       => null,
            'worker_id'        => $worker->id,
            'type_document_id' => $request->input('type_document_id'),
        ]);

        return response()->json($doc->load('typeDocument'), 200);
    }

    public function update(Request $request, $id)
    {
        $doc = Document::where('id', $id)->whereNotNull('worker_id')->first();
        if (! $doc) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }
        $v = Validator::make($request->all(), [
            'description'       => 'sometimes|string|max:255',
            'type_document_id'  => 'nullable|integer|exists:type_documents,id',
            'number'            => 'nullable|string',
            'dueDate'           => 'nullable|date',
            'pathFile'          => 'nullable|file|max:10240',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $data = $request->only(['description', 'type_document_id', 'number', 'dueDate']);
        if ($request->hasFile('pathFile')) {
            if ($doc->pathFile) {
                $old = str_replace('/storage', 'public', $doc->pathFile);
                Storage::delete($old);
            }
            $filePath           = $request->file('pathFile')->store('public/workerDocuments/' . $doc->worker_id);
            $data['pathFile']   = Storage::url($filePath);
        }
        $doc->update(array_filter($data, fn ($x) => $x !== null));

        return response()->json($doc->fresh(['typeDocument']));
    }

    public function destroy($id)
    {
        $doc = Document::where('id', $id)->whereNotNull('worker_id')->first();
        if (! $doc) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }
        if ($doc->pathFile) {
            $old = str_replace('/storage', 'public', $doc->pathFile);
            Storage::delete($old);
        }
        $doc->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
