<?php

namespace BooneStudios\Surreal\Schema;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{
    /**
     * The SurrealDB connection instance.
     *
     * @var \BooneStudios\Surreal\Schema\Connection
     */
    protected $connection;

    /**
     * @inheritDoc
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function index($columns = null, $name = null, $algorithm = null)
    {
        $columns = $this->fluent($columns);



        return $this;
    }
}
