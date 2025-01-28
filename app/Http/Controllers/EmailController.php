<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmationMail;
use App\Models\Moviment;
use App\Services\CarrierGuideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    protected $carrierGuideService;

    public function __construct(CarrierGuideService $CarrierGuideService)
    {
        $this->carrierGuideService = $CarrierGuideService;
    }

    public function validatemail(Request $request)
    {
  
        $username   = Auth::user()->username??"AdminPost";
        $correoSend = "guevaracajusolmiguel@gmail.com";
        $token      = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT); // Token de 8 dígitos
        Cache::put("username_verification_token:{$username}", $token, 300);   //5 minutos
        Mail::to($correoSend)->send(new ConfirmationMail($token));
        return response()->json(['status' => 'success'], 200);
    }

    public function desvincularGuideSale(Request $request)
    {
        $username    = Auth::user()->username;
        $token       = $request->token;
        $moviment_id = $request->moviment_id;
        $moviment=Moviment::find($moviment_id);
        if(!$moviment){
            return response()->json(['message' => 'Venta No Encontrado'], 422);
        }
        if($moviment->status_facturado =="Anulada"){
            return response()->json(['message' => 'Venta ya Anulada'], 422);
        }
    
     
        $moviment    = null;
        $cachedToken = Cache::get("username_verification_token:{$username}");
        if ($cachedToken != $token) {
            return response()->json(['message' => 'Su token ha vencido, Debe generar nuevo token'], 422);
        }
        $moviment = $this->carrierGuideService->desvincularGuideSale($moviment_id);

        Cache::forget("username_verification_token:{$username}");
        return response($moviment, 200);
    }

}
