<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use BooneStudios\Surreal\Connection;
use BooneStudios\Surreal\Query\Builder;

test('connection', function () {
    $connection = DB::connection('surrealdb');

    expect($connection)->toBeInstanceOf(Connection::class);
});
