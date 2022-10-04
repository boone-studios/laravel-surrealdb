<?php

namespace BooneStudios\Surreal\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;
use Illuminate\Support\Arr;

class Processor extends BaseProcessor
{
    /**
     * @inheritDoc
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();
        $connection->insert($sql, $values);

        $id = Arr::get($connection->getLastResults(), 'result.0.id');

        return is_numeric($id) ? (int) $id : $id;
    }
}
