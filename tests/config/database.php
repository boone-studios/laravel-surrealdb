<?php

$surrealNamespace = env('SURREALDB_NAMESPACE', 'test');
$surrealHost      = env('SURREALDB_HOST', 'localhost');
$surrealPort      = env('SURREALDB_PORT', '8000');
$surrealUsername  = env('SURREALDB_USERNAME', 'root');
$surrealPassword  = env('SURREALDB_PASSWORD', 'root');
$surrealDatabase  = env('SURREALDB_DATABASE', 'test');

return [

    'connections' => [

        'surrealdb' => [
            'namespace' => $surrealNamespace,
            'driver'    => 'surrealdb',
            'host'      => $surrealHost,
            'port'      => $surrealPort,
            'database'  => $surrealDatabase,
            'username'  => $surrealUsername,
            'password'  => $surrealPassword,
        ],

    ],

];
