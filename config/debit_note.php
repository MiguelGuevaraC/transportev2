<?php

$enviarDefecto = in_array(env('APP_ENV', 'local'), ['local', 'testing'], true) ? 'false' : 'true';

return [

    'enviar_al_facturador' => filter_var(
        env('DEBIT_NOTE_ENVIAR_AL_FACTURADOR', $enviarDefecto),
        FILTER_VALIDATE_BOOL
    ),

];
