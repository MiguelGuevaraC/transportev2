<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $moviment;

    /**
     * Crea una nueva instancia del mailable.
     *
     * @param string $token
     */
    public function __construct($token,$moviment)
    {
        $this->token = $token;
        $this->moviment = $moviment;
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        $moviment_number = $this->moviment->sequentialNumber;
        $receptions = $this->moviment->receptions;
    
        // Validamos si hay recepciones antes de acceder a ellas
        $receptionsData = [];
    
        if ($receptions && count($receptions) > 0) {
            foreach ($receptions as $reception) {
                $receptionsData[] = [
                    'codeReception' => $reception->codeReception ?? 'Sin Recepción',
                    'guideNumber' => $reception->firstCarrierGuide->numero ?? 'Sin guía'
                ];
            }
        }
  
        return $this->view('emails.token')  // Vista del correo
            ->subject('Tu Token de Verificación') // Asunto del correo
            ->with([
                'token' => $this->token,
                'moviment_number' => $moviment_number,
                'receptions' => $receptionsData  // Se envía el listado de recepciones y guías
            ]);
    }
    
}
