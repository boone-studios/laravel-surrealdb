<?php

declare(strict_types=1);

use BooneStudios\Surreal\Connection;
use Illuminate\Support\Facades\DB;

it('can connect to SurrealDB', function () {
    $connection = DB::connection('surrealdb');

    expect($connection)->toBeInstanceOf(Connection::class);
});
