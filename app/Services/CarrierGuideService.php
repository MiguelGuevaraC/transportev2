<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use App\Models\Moviment;
use App\Models\Product;
use App\Models\Reception;
use App\Models\ReceptionBySale;

class CarrierGuideService
{
    public function desvincularGuideSale($sale_id): Moviment
    {

        $moviment   = Moviment::find($sale_id);
        $receptions = $moviment->receptions;

        foreach ($receptions as $reception) {

            $receptionBySale = ReceptionBySale::where('reception_id', $reception->id)
                ->where('moviment_id', $moviment->id)
                ->first();
            if ($receptionBySale) {
                $receptionBySale->status = 'Anulada por Devinculación';
                $receptionBySale->save();
            }

            $reception              = Reception::find($reception->id);
            $reception->moviment_id = null;
            $reception->save();
        }
        $moviment->status           = "Anulado";
        $moviment->status_facturado = "Anulada";
        $moviment->save();
        return $moviment;
    }


    

}
