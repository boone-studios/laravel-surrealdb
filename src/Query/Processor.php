<?php

namespace BooneStudios\Surreal\Query;

use BooneStudios\Surreal\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;
use Illuminate\Support\Arr;

class Processor extends BaseProcessor
{
    /**
     * {@inheritDoc}
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        /** @var Connection $connection */
        $connection = $query->getConnection();
        $connection->insert($sql, $values);

        $id = Arr::get($connection->getLastResults(), 'result.0.id', Arr::get($connection->getLastResults(), 'result.id'));

        return is_numeric($id) ? (int) $id : $id;
    }
}
