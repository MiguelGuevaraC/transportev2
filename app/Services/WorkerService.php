<?php
namespace App\Services;

use App\Models\Worker;

class WorkerService
{

    public function getWorkerById(int $id): ?Worker
    {
        return Worker::find($id);
    }

    public function createWorker(array $data): Worker
    {
        $proyect = Worker::create($data);
        return $proyect;
    }

    public function updateWorker(Worker $proyect, array $data): Worker
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return Worker::find($id)?->delete() ?? false;
    }
    public function changeStatus($id)
    {
        $worker = Worker::find($id);
        if (! $worker) {
            return false;
        }
        $worker->state = $worker->state == 0 ? 1 : 0;
        $worker->save();
        return $worker;
    }

}
