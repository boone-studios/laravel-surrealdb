<?php

$surrealNamespace = env('SURREALDB_NAMESPACE', 'test');
$surrealProtocol = env('SURREALDB_PROTOCOL', 'http');
$surrealHost = env('SURREALDB_HOST', 'localhost');
$surrealPort = env('SURREALDB_PORT', '8000');
$surrealUrl = env('SURREALDB_URL');
$surrealUsername = env('SURREALDB_USERNAME', 'root');
$surrealPassword = env('SURREALDB_PASSWORD', 'root');
$surrealDatabase = env('SURREALDB_DATABASE', 'test');

return [
    'connections' => [
        'surrealdb' => [
            'namespace' => $surrealNamespace,
            'driver' => 'surrealdb',
            'protocol' => $surrealProtocol,
            'host' => $surrealHost,
            'port' => $surrealPort,
            'url' => $surrealUrl,
            'database' => $surrealDatabase,
            'username' => $surrealUsername,
            'password' => $surrealPassword,
            'bootstrap' => true,
        ],
    ],
];
