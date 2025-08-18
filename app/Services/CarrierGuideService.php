<?php
namespace App\Services;

use App\Exports\guides\GuidesIntegradoExport;
use App\Http\Resources\CarrierGuideResource;
use App\Models\CarrierGuide;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\Product;
use App\Models\Reception;
use App\Models\ReceptionBySale;
use Maatwebsite\Excel\Facades\Excel;

class CarrierGuideService
{
    protected $commonService;
    protected $productService;

    public function __construct(CommonService $commonService, ProductService $productService)
    {
        $this->commonService  = $commonService;
        $this->productService = $productService;
    }
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

        //eliminar las installments de ese movimientos
        Installment::where('moviment_id', $moviment->id)->delete();
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
    public function updatestockProduct($carrier_id)
    {
        $carrier   = CarrierGuide::findOrFail($carrier_id);
        $id_branch = $carrier->reception->branchOffice_id;
        $carrier->reception->details->each(function ($detail) use ($id_branch) {
            if (isset($detail->product_id)) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $this->productService->updatestock($product, $id_branch);
                }
            }
        });
    }

    public function export(array $data, string $filename = 'guias_por_serie.xlsx')
    {
        return Excel::download(new GuidesIntegradoExport($data), $filename);
    }

    public function update_path(array $data, CarrierGuide $carrierGuide)
    {
        try {
            $fillableData = array_intersect_key($data, array_flip($carrierGuide->getFillable()));
            if ($carrierGuide->update($fillableData)) {
                return ['success' => true, 'data' => new CarrierGuideResource($carrierGuide)];
            }
            return ['success' => false, 'data' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => null];
        }
    }

}
