<?php

// Parámetros de la base de datos MySQL de destino (red local).
// Se leen desde variables de entorno (.env). NUNCA escribir credenciales aquí.
return [
    'host'     => env('DEST_DB_HOST', '10.0.40.136'),
    'port'     => env('DEST_DB_PORT', '3306'),
    'database' => env('DEST_DB_DATABASE'),
    'username' => env('DEST_DB_USERNAME'),
    'password' => env('DEST_DB_PASSWORD'),
    'table'    => env('DEST_DB_TABLE', 'solicitudes_soporte'),
    'grupo'    => env('GRUPO_NOMBRE', 'bacarlix'),
];
