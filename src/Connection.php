<?php

namespace BooneStudios\Surreal;

use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Surreal\Surreal;
use Surreal\Surreal as SurrealClient;

class Connection extends BaseConnection
{
    /**
     * The Surreal SDK client.
     *
     * @var Surreal
     */
    protected $connection;

    /*
     * The last results from a query.
     *
     * @param array $results
     */
    protected $lastResults;

    /**
     * Bind values to their parameters in the given query.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return string
     */
    protected function bindQueryParams($query, $bindings)
    {
        foreach ($this->prepareBindings($bindings) as $key => $value) {
            if (is_string($value)) {
                $value = "'".addslashes($value)."'";
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $value = 'null';
            }

            $query = Str::replaceFirst('?', $value, $query);
        }

        return $query;
    }

    /**
     * Create a new SurrealDB connection.
     *
     *
     * @return Surreal
     */
    protected function createConnection(array $config, array $options)
    {
        $protocol = Arr::get($config, 'protocol', 'http');
        $host = Arr::get($config, 'host', '127.0.0.1');
        $port = Arr::get($config, 'port', '8000');
        $url = Arr::get($config, 'url');

        if (! $url) {
            $hasScheme = parse_url((string) $host, PHP_URL_SCHEME) !== null;
            $base = $hasScheme ? $host : sprintf('%s://%s', $protocol, $host);
            $hasPort = parse_url((string) $base, PHP_URL_PORT) !== null;
            $url = $hasPort ? $base : sprintf('%s:%s', $base, $port);
        }

        $client = new SurrealClient;
        $namespace = Arr::get($config, 'namespace');
        $database = Arr::get($config, 'database');
        $client->connect($url, [
            'namespace' => $namespace,
            'database' => $database,
        ]);

        if ($token = Arr::get($config, 'token')) {
            $client->authenticate($token);
        } elseif (Arr::get($config, 'username') && Arr::get($config, 'password')) {
            $client->signin([
                'user' => Arr::get($config, 'username'),
                'pass' => Arr::get($config, 'password'),
            ]);
        }

        $this->configureContext($client, $namespace, $database, (bool) Arr::get($config, 'bootstrap', false));

        return $client;
    }

    /**
     * Create a new database connection instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $options = Arr::get($config, 'options', []);

        $this->connection = $this->createConnection($config, $options);

        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();
        $this->useDefaultSchemaGrammar();
    }

    /**
     * {@inheritDoc}
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            $query = Str::finish($query, ' return count()');
            $query = $this->bindQueryParams($query, $bindings);
            $compiledQuery = (string) $query;

            $response = $this->decode($this->connection->query($compiledQuery));
            $this->lastResults = $this->normalizeResultPayload($response);
            $this->throwOnError($compiledQuery, $this->lastResults);

            $this->recordsHaveBeenModified(
                ($count = Arr::get($this->lastResults, 'result.0.count', Arr::get($this->lastResults, 'result.count', 0))) > 0
            );

            return $count;
        });
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string  $collection
     * @return Query\Builder
     */
    public function collection($collection)
    {
        $query = new Query\Builder($this, $this->getDefaultQueryGrammar(), $this->getPostProcessor());

        return $query->from($collection);
    }

    /**
     * Decode the response from the SurrealDB server.
     *
     * @param  mixed  $response
     * @return mixed
     *
     * @throws \JsonException
     */
    public function decode($response)
    {
        if (is_array($response)) {
            return $response;
        }

        if (is_object($response) && method_exists($response, 'toArray')) {
            return $response->toArray();
        }

        return ['status' => 'OK', 'result' => $response];
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'surrealdb';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPostProcessor()
    {
        return new Query\Processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultQueryGrammar()
    {
        return new Query\Grammar($this);
    }

    /**
     * Return the last results from a query.
     *
     * @return array
     */
    public function getLastResults()
    {
        return $this->lastResults;
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array|mixed
     */
    public function select($query, $bindings = [], $useReadPdo = false)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $query = $this->bindQueryParams($query, $bindings);
            $compiledQuery = (string) $query;
            $response = $this->decode($this->connection->query($compiledQuery));
            $this->lastResults = $this->normalizeResultPayload($response);
            $this->throwOnError($compiledQuery, $this->lastResults);

            return $this->lastResults;
        });
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $query = $this->bindQueryParams($query, $bindings);
            $compiledQuery = (string) $query;
            $response = $this->decode($this->connection->query($compiledQuery));

            $this->recordsHaveBeenModified();

            $this->lastResults = $this->normalizeResultPayload($response);
            $this->throwOnError($compiledQuery, $this->lastResults);

            return Arr::get($this->lastResults, 'status', 'OK') === 'OK';
        });
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string  $table
     * @param  ?string  $as
     * @return Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this->collection($table);
    }

    /**
     * Normalize statement response to a stable shape.
     *
     * @param  mixed  $result
     * @return array
     */
    protected function normalizeResultPayload($result)
    {
        if (! is_array($result)) {
            return ['status' => 'OK', 'result' => $this->normalizeSdkValue($result)];
        }

        if (Arr::isList($result)) {
            if (is_string(Arr::first($result))) {
                return ['status' => 'ERR', 'result' => $result];
            }

            $first = Arr::first($result);

            if (is_array($first) && Arr::isList($first)) {
                return ['status' => 'OK', 'result' => $this->normalizeSdkValue($first)];
            }

            if (is_array($first) && array_key_exists('status', $first) && array_key_exists('result', $first)) {
                return $first;
            }

            return ['status' => 'OK', 'result' => $this->normalizeSdkValue($result)];
        }

        if (! array_key_exists('result', $result)) {
            $result = ['status' => 'OK', 'result' => $result];
        }

        if (! array_key_exists('status', $result)) {
            $result['status'] = 'OK';
        }

        $result['result'] = $this->normalizeSdkValue($result['result']);

        return $result;
    }

    /**
     * Normalize SDK return values into scalar/array primitives.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function normalizeSdkValue($value)
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeSdkValue($item), $value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Throw when the SDK reports a query error.
     *
     * @param  string  $query
     * @return void
     */
    protected function throwOnError($query, array $payload)
    {
        if (Arr::get($payload, 'status') !== 'ERR') {
            return;
        }

        $errors = Arr::wrap(Arr::get($payload, 'result', []));
        $message = Arr::first($errors) ?: 'Unknown SurrealDB query error.';

        throw new RuntimeException(sprintf('SurrealDB query failed: %s (query: %s)', $message, $query));
    }

    /**
     * Configure namespace/database context for the connection.
     *
     * @param  ?string  $namespace
     * @param  ?string  $database
     * @param  bool  $bootstrap
     * @return void
     */
    protected function configureContext(SurrealClient $client, $namespace, $database, $bootstrap)
    {
        if (! $namespace || ! $database) {
            return;
        }

        if ($bootstrap) {
            // Useful for tests/local development where NS/DB may be absent.
            $client->query(sprintf('DEFINE NAMESPACE IF NOT EXISTS %s;', $namespace));
            $client->query(sprintf('DEFINE DATABASE IF NOT EXISTS %s;', $database));
        }

        $client->use([
            'namespace' => $namespace,
            'database' => $database,
        ]);
    }
}
