<?php
namespace App\Http\Controllers\AccountPayable;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayableResource;
use App\Models\Payable;
use App\Models\PayPayable;

class PayPayableController extends Controller
{
    public function destroy($id)
    {

        $object = PayPayable::find($id);
        if (! $object) {
            return response()->json(['message' => 'Amortización no Encontrada'], 404); // Cambiado a 404 para mejor claridad
        }

        $payable = Payable::find($object->payable_id);
        // if ($object->latest_bank_movement_transaction) {
        //     if ($object->latest_bank_movement->status === "Confirmado") {
        //         return response()->json([
        //             'message' => 'El ingreso a caja grande ya fue confirmado y no se puede eliminar.'
        //         ], 422);
        //     }
        // }

                           // $venta = Moviment::find($payable->moviment_id);
                           // if ($venta->status == "Anulada") {
                           //     return response()->json(['message' => 'La Venta está Anulada'], 422); // Cambiado a 404 para mejor claridad
                           // }
        $object->delete(); // Cambia esto si se necesita otra lógica

        $payable->updateMontos();
        // if ($object->latest_bank_movement_anticipo) {

        //     if ($object->latest_bank_movement->transaction_concept_id == 1) {
        //         $movement_anticipo = BankMovement::find($object->latest_bank_movement->id);
        //         $movement_anticipo->update_montos_anticipo();
        //     }
        // }

        // if($object->latest_bank_movement_transaction){
        //     {
        //         $movement = BankMovement::find($object->latest_bank_movement->id);
        //         $movement->delete();
        //     }
        // }
        return response()->json(['payable' => new PayableResource(Payable::find($payable->id))], 200);
    }
}
