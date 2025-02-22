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

    /**
     * @OA\Post(
     *     path="/transporte/public/api/validatetoken",
     *     summary="Enviar código de verificación por correo",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="moviment_id", type="integer", example=101)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código enviado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     )
     * )
     */

     public function validatemail(Request $request)
     {
         $moviment_id = $request->moviment_id;
         $moviment    = Moviment::find($moviment_id);
         if (! $moviment) {
             return response()->json(['message' => 'Venta No Encontrado'], 422);
         }
     
         $username   = Auth::user()->username ?? "AdminPost";
         $correoSend = "guevaracajusolmiguel@gmail.com";
         $correoSend2 = "alvarorent2001@gmail.com";
         $token      = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT); // Token de 8 dígitos
         Cache::put("username_verification_token:{$username}", $token, 300);   //5 minutos
         Mail::to([$correoSend, $correoSend2])->send(new ConfirmationMail($token, $moviment));
         return response()->json(['status' => 'success'], 200);
     }
     
/**
 * @OA\Post(
 *     path="/transporte/public/api/desvinculatesale",
 *     summary="Desvincular guía de venta",
 *     description="Desvincula una guía de una venta específica si cumple con las condiciones. Verifica el token y el estado de la venta antes de proceder.",
 *     tags={"Sale"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="12345678"),
 *             @OA\Property(property="moviment_id", type="integer", example=101)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Guía desvinculada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="moviment", type="object", description="Información actualizada de la venta")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Venta No Encontrado")
 *         )
 *     )
 * )
 */
    public function desvincularGuideSale(Request $request)
    {
        $username    = Auth::user()->username;
        $token       = $request->token;
        $moviment_id = $request->moviment_id;
        $moviment    = Moviment::find($moviment_id);
        if (! $moviment) {
            return response()->json(['message' => 'Venta No Encontrado'], 422);
        }
        if ($moviment->status_facturado == "Anulada") {
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
