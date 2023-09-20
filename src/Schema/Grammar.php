<?php

namespace BooneStudios\Surreal\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar as BaseGrammar;
use Illuminate\Support\Fluent;

class Grammar extends BaseGrammar
{
    /**
     * If this Grammar supports schema changes wrapped in a transaction.
     *
     * @var bool
     */
    protected $transactions = true;

    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected $modifiers = [
        'default',
        'required',
    ];

    /**
     * The columns available as serials.
     *
     * @var string[]
     */
    protected $serials = [
        'enum',
    ];

    /**
     * Compile the blueprint's column definitions.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @return array
     */
    protected function getColumns(Blueprint $blueprint)
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            $sql = 'define field ' . $this->wrap($column) . ' ' . $this->getType($column);

            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return $columns;
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent            $column
     * @return string|null
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' assert $value or ' . $this->getDefaultValue($column->default);
        }
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent            $column
     * @return string|null
     */
    protected function modifyRequired(Blueprint $blueprint, Fluent $column)
    {
        return $column->nullable ? '' : ' assert $value != none';
    }

    /**
     * Create the column definition for an enumeration type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        return sprintf(
            'assert $value inside [%s]',
            $column->name,
            $this->quoteString($column->allowed),
        );
    }

    /**
     * Compile a column addition command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $columns   = [];
        $tableName = $this->wrapTable($blueprint);

        foreach ($this->getColumns($blueprint) as $column) {
            $columns = sprintf(
                'define field %s on table %s',
                $column,
                $tableName,
            );
        }

        return impode('; ', $columns);
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        return sprintf(
            'define table %s schemafull; %s',
            $this->wrapTable($blueprint),
            implode('; ', $this->getColumns($blueprint))
        );
    }

    /**
     * Compile a create database command.
     *
     * @param string                          $name
     * @param \Illuminate\Database\Connection $connection
     * @return string
     */
    public function compileCreateDatabase($name, $connection)
    {
        return sprintf('define database %s', $this->wrapValue($name));
    }

    /**
     * Compile a drop table command.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent            $command
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('remove table %s', $blueprint->getTable());
    }

    /**
     * Compile a drop database if exists command.
     *
     * @param  string  $name
     * @return string
     */
    public function compileDropDatabaseIfExists($name)
    {
        return sprintf(
            'remove table %s',
            $this->wrapValue($name)
        );
    }

    /**
     * Compile a drop index command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf(
            'remove index %s on table %s',
            $this->wrap($command->index),
            $blueprint->getTable()
       );
    }

    /**
     * Compile the SQL needed to retrieve all table names.
     *
     * @param  string|array  $searchPath
     * @return string
     */
    public function compileGetAllTables($searchPath)
    {
        $databaseName = is_array($searchPath) ? $searchPath[0] : $searchPath;

        return "info for database $databaseName";
    }

    /**
     * Compile a plain index key command.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent            $command
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf(
            'define index %s on %s (%s)',
            $command->index,
            $blueprint->getTable(),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile the query to determine if a table exists.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'remove table ?';
    }
}
