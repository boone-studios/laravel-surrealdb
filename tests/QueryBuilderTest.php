<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

test('get', function () {
    $users = DB::table('users')->get();
    $this->assertCount(0, $users);

    DB::table('users')->insert([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $users = DB::table('users')->get();
    $this->assertCount(1, $users);
});
