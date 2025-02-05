<?php
namespace App\Services;

use App\Models\CarrierGuide;
use App\Models\Moviment;
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

    public function updatedatasubcontrata($carrier_id, $cost, $data): CarrierGuide
    {
        $carrier = CarrierGuide::findOrFail($carrier_id);

        $carrier->update([
            'costsubcontract' => $cost,
            'datasubcontract' => json_encode($data),
        ]);

        return $carrier;
    }
    

}
