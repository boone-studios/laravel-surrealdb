<?php

namespace BooneStudios\Surreal\Schema;

use Illuminate\Database\Schema\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * Create a database in the schema.
     *
     * @param  string  $name
     * @return bool
     */
    public function createDatabase($name)
    {
        return $this->connection->statement(
            $this->grammar->compileCreateDatabase($name, $this->connection)
        );
    }
}
