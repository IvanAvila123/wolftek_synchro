<?php

return [
    'private_key' => env('CONEKTA_PRIVATE_KEY', ''),
    'public_key'  => env('CONEKTA_PUBLIC_KEY', ''),

    /*
     * Llave pública RSA para verificar la firma de webhooks.
     * Guarda solo la parte base64 en .env (sin las líneas -----BEGIN/END-----).
     */
    'webhook_public_key' => env('CONEKTA_WEBHOOK_PUBLIC_KEY', ''),
];
