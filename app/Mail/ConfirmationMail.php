<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Crea una nueva instancia del mailable.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.token')     // Vista del correo
            ->subject('Tu Token de Verificación') // Asunto del correo
            ->with(['token' => $this->token]);     // Datos enviados a la vista
    }
}
