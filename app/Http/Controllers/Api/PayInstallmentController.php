<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Box;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\PayInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayInstallmentController extends Controller
{

    public function generateMovBox(Request $request, $id)
    {

        $object = PayInstallment::find($id);
        if (!$object) {
            return response()->json(['message' => 'Amortización no Encontrada'], 404); // Cambiado a 404 para mejor claridad
        }
        $person_id = $object?->installment?->moviment?->person_id ?? 2;

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json(['message' => 'Este Usuario no tiene Una Caja Asignada'], 404); // Cambiado a 404 para mejor claridad
            }else{
                if ($box->status !== 'Activa') { // Ajusta el valor 'active' según tu lógica de estado
                    return response()->json(['message' => 'La caja asignada no está activa'], 403); // Código HTTP 403: Prohibido
                }
            }
           
        }
        $efectivo = $object->cash ?? 0;
        $yape = $object->yape ?? 0;
        $plin = $object->plin ?? 0;
        $tarjeta = $object->card ?? 0;
        $deposito = $object->deposit ?? 0;
        $comment = $object->comment ?? '-';
        // $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        if ($efectivo <=0) {
            return response()->json(['message' => 'La amortización debe ser en Efectivo'], 422); // Cambiado a 404 para mejor claridad
        }

        $tipo = 'M' . str_pad($box_id, 3, '0', STR_PAD_LEFT);

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(correlative, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
             FROM moviments
             WHERE movType = "Caja"
             AND SUBSTRING_INDEX(correlative, "-", 1) = ?',
            [$tipo]
        );

        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        $data = [
            'correlative' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'sequentialNumber' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate' => $object->paymentDate,
            'total' => $object->total ?? 0,
            'yape' => $yape,
            'deposit' => $deposito,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,

            'comment' => $comment ?? '-',
            'typeDocument' => 'Ingreso',
            'movType' => 'Caja',
            'typeCaja' => 'Amortizacion',
            'operationNumber' => $object->nroOperacion ?? '',
            'typePayment' => $request->input('typePayment') ?? null,
            'typeSale' => $request->input('typeSale') ?? '-',
            'status' => 'Generada',
            'programming_id' => $request->input('programming_id'),
            'paymentConcept_id' => 12, //Ingresos Varios
            'branchOffice_id' => $box->branchOffice_id ?? null,
            'reception_id' => $request->input('reception_id'),
            'person_id' => $person_id,
            'user_id' => auth()->id(),
            'pay_installment_id' => $object->id ?? null,
            'box_id' => $box_id,
        ];

        $mov = Moviment::create($data);

        $object = PayInstallment::with(['movement', 'bank'])->find($id);

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'POST', // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Generar mov Caja de una Amortización', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

  

        return response()->json(['PayInstallment' => $object], 200);
    }
    public function destroy($id)
    {

        $object = PayInstallment::find($id);
        if (!$object) {
            return response()->json(['message' => 'Amortización no Encontrada'], 404); // Cambiado a 404 para mejor claridad
        }

        $installment = Installment::find($object->installment_id);

        $venta = Moviment::find($installment->moviment_id);
        // if ($venta->status == "Anulada") {
        //     return response()->json(['message' => 'La Venta está Anulada'], 422); // Cambiado a 404 para mejor claridad
        // }
        $object->delete(); // Cambia esto si se necesita otra lógica

        $installment->updateMontos();

        $installment = Installment::with(['moviment', 'moviment.person', 'payInstallments',
            'payInstallments.bank'])->find($installment->id);

        return response()->json(['installment' => $installment], 200);
    }
}
