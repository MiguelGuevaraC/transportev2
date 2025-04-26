<?php
namespace App\Services;

use App\Models\CheckList;
use Illuminate\Support\Facades\DB;

class CheckListService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCheckListById(int $id): ?CheckList
    {
        return CheckList::find($id);
    }

    public function createCheckList(array $data): CheckList
    {
        // Asignar un estado por defecto
        $data['status'] = 'Activo';

        // Crear el checklist
        $checkList = CheckList::create($data);

        // Generación del número de checklist
        $tipo         = 'CL' . str_pad($data['branch_office_id'], 2, '0', STR_PAD_LEFT);
        $tipo         = str_pad($tipo, 4, '0', STR_PAD_RIGHT);
        $siguienteNum = DB::selectOne('
        SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum
        FROM check_lists
        WHERE SUBSTRING(numero, 1, 4) = ?
    ', [$tipo])->siguienteNum;

        $checkList->numero = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
        $checkList->save();

        // Verificar si hay ítems para asociar
        if (isset($data['check_list_item_ids']) && is_array($data['check_list_item_ids'])) {
            // Asegúrate de que los ítems proporcionados existen y asociarlos con el checklist
            $checkList->checkListItems()->sync($data['check_list_item_ids']);
        }

        return $checkList;
    }

    public function updateCheckList(CheckList $checkList, array $data): CheckList
    {
        // Encontrar el checklist por ID

        // Actualizar los datos básicos del checklist
        $checkList->update([
            'branch_office_id' => $data['branch_office_id'] ?? $checkList->branch_office_id,
            'date_check_list'  => $data['date_check_list'] ?? $checkList->date_check_list,
            'vehicle_id'       => $data['vehicle_id'] ?? $checkList->vehicle_id,
            'observation'      => $data['observation'] ?? $checkList->observation,
            'status'           => $data['status'] ?? $checkList->status,
        ]);


        if (isset($data['check_list_item_ids']) && is_array($data['check_list_item_ids'])) {
            $checkList->checkListItems()->sync($data['check_list_item_ids']);
        }

        return $checkList;
    }

    public function destroyById($id)
    {
        return CheckList::find($id)?->delete() ?? false;
    }

}
