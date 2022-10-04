<?php

namespace BooneStudios\Surreal\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use RuntimeException;

class Grammar extends BaseGrammar
{
    /**
     * Convert an array of column names into a delimited string.
     *
     * @param array $columns
     * @return string
     */
    public function columnize(array $columns)
    {
        return implode(', ', $columns);
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values)
    {
        if (empty($values)) {
            throw new RuntimeException('Cannot insert empty values');
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $parameters = collect($values)->map(function ($record) {
                $statements = [];

                foreach ($record as $key => $value) {
                    $statements[] = "{$key} = ?";
                }

                return implode(', ', $statements);
            })->implode(' AND ');

        return "create $query->from set $parameters";
    }

    /**
     * Compile a select query into SQL.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (($query->unions || $query->havings) && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }

        // If the query does not have any columns set, we'll set the columns to the
        // * character to just get all the columns from the database. Then we
        // can build the query and concatenate all the pieces together as one.
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the SQL.
        $sql = trim($this->concatenate($this->compileComponents($query)));

        if ($query->unions) {
            $sql = $this->wrapUnion($sql) . ' ' . $this->compileUnions($query);
        }

        $query->columns = $original;

        return $sql;
    }
}
