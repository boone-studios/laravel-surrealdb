<?php

namespace BooneStudios\Surreal\Connectors;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Queue\Connectors\ConnectorInterface;

class SurrealConnector extends Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \GuzzleHttp\Client;
     */
    public function connect(array $config)
    {
        $clientConfig = [
            'base_uri' => $config['base_uri'],
            'headers'  => [
                'Content-Type' => 'application/json',
                'NS'           => $config['namespace'],
                'DB'           => $config['database'],
            ],
        ];

        // Both username and password are required for Basic Auth
        if (isset($config['username'], $config['password'])) {
            $clientConfig['auth'] = [
                $config['username'],
                $config['password'] ?? '',
            ];
        }

        return new GuzzleClient($clientConfig);
    }
}
