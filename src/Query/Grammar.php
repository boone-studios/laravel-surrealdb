<?php

namespace BooneStudios\Surreal\Query;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use RuntimeException;

class Grammar extends BaseGrammar
{
    /**
     * Create grammar and stay compatible across Laravel versions.
     *
     * @param  Connection|null  $connection
     */
    public function __construct($connection = null)
    {
        if ($connection) {
            $this->connection = $connection;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function compileDeleteWithJoins(Builder $query, $table, $where)
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($query, $query->joins);

        return "delete {$alias} {$table} {$joins} {$where}";
    }

    /**
     * {@inheritDoc}
     */
    protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
    {
        return "delete {$table} {$where}";
    }

    /**
     * {@inheritDoc}
     */
    public function columnize(array $columns)
    {
        return implode(', ', $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function compileInsert(Builder $query, array $values)
    {
        if (empty($values)) {
            throw new RuntimeException('Cannot insert empty values');
        }

        $record = is_array(reset($values)) ? reset($values) : $values;
        $parameters = collect($record)->map(function ($value, $key) {
            return "{$key} = ?";
        })->implode(', ');

        return "create $query->from set $parameters return after";
    }

    /**
     * {@inheritDoc}
     */
    protected function compileUpdateColumns(Builder $query, array $values)
    {
        return collect($values)->map(function ($value, $key) {
            return $key.' = '.$this->parameter($value);
        })->implode(', ');
    }

    /**
     * {@inheritDoc}
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
            $sql = $this->wrapUnion($sql).' '.$this->compileUnions($query);
        }

        $query->columns = $original;

        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function compileWheres(Builder $query)
    {
        if (is_null($query->wheres)) {
            return '';
        }

        $clauses = collect($query->wheres)->map(function ($where) use ($query) {
            return $where['boolean'].' '.$this->{"where{$where['type']}"}($query, $where);
        })->values();

        if ($clauses->isEmpty()) {
            return '';
        }

        $first = preg_replace('/^(and|or)\s+/i', '', (string) $clauses->shift());

        return trim('where '.$first.' '.$clauses->implode(' '));
    }

    /**
     * {@inheritDoc}
     */
    public function compileTruncate(Builder $query)
    {
        return ['delete '.$this->wrapTable($query->from) => []];
    }

    /**
     * {@inheritDoc}
     */
    public function compileUpdate(Builder $query, array $values)
    {
        $columns = $this->compileUpdateColumns($query, $values);

        $where = $this->compileWheres($query);

        return trim(
            isset($query->joins)
                ? $this->compileUpdateWithJoins($query, $query->from, $columns, $where)
                : $this->compileUpdateWithoutJoins($query, $query->from, $columns, $where)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return $where['column'].' '.$operator.' '.$value;
    }

    /**
     * {@inheritDoc}
     */
    protected function whereBetween(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

        $max = $this->parameter(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return $where['column'].' '.$between.' '.$min.' and '.$max;
    }

    /**
     * {@inheritDoc}
     */
    protected function whereBetweenColumns(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->wrap(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

        $max = $this->wrap(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return $where['column'].' '.$between.' '.$min.' and '.$max;
    }

    /**
     * {@inheritDoc}
     */
    protected function whereIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $where['column'].' inside ['.$this->parameterize($where['values']).']';
        }

        return '0 = 1';
    }

    /**
     * {@inheritDoc}
     */
    protected function whereInRaw(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $where['column'].' inside ['.implode(', ', $where['values']).']';
        }

        return '0 = 1';
    }

    /**
     * {@inheritDoc}
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $where['column'].' not inside ['.$this->parameterize($where['values']).']';
        }

        return '1 = 1';
    }

    /**
     * {@inheritDoc}
     */
    protected function whereNotInRaw(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $where['column'].' not inside ['.implode(', ', $where['values']).']';
        }

        return '1 = 1';
    }

    /**
     * {@inheritDoc}
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return $where['column'].' is not null';
    }

    /**
     * {@inheritDoc}
     */
    protected function whereNull(Builder $query, $where)
    {
        return $where['column'].' is null';
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return str_replace('"', '""', $value);
        }

        return $value;
    }
}
