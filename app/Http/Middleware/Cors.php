<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // Permitir solicitudes desde cualquier origen
        $headers = [
            'Access-Control-Allow-Origin' => 'https://transportes-hernandez.vercel.app',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin,Content-Type, Content-Type, Accept, Authorization,X-Requested-With',
            'Access-Control-Allow-Credentials' => true,
        ];

        // Verificar si es una solicitud OPTIONS
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
        }

        // Continuar con la solicitud
        $response = $next($request);

        // Agregar las cabeceras CORS a la respuesta
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
