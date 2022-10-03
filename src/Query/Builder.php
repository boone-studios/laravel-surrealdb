<?php

namespace BooneStudios\Surreal\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * All the available clause operators.
     *
     * @var string[]
     */
    public $operators = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '!=',
        'like',
        'not like',
        'between',
        '&',
    ];

    /**
     * @inheritdoc
     */
    public function __construct(Connection $connection, Processor $processor)
    {
        $this->grammar    = new Grammar();
        $this->connection = $connection;
        $this->processor  = $processor;
    }

    /**
     * Generate the unique cache key for the current query.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        $key = [
            'connection' => $this->collection->getDatabaseName(),
            'collection' => $this->collection->getCollectionName(),
            'wheres'     => $this->wheres,
            'columns'    => $this->columns,
            'groups'     => $this->groups,
            'orders'     => $this->orders,
            'offset'     => $this->offset,
            'limit'      => $this->limit,
            'aggregate'  => $this->aggregate,
        ];

        return md5(serialize(array_values($key)));
    }
}
