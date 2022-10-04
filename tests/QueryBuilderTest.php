<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('users')->truncate();
});

it('can create records', function () {
    $users = DB::table('users')->get();
    $this->assertCount(0, $users);

    DB::table('users')->insert([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $users = DB::table('users')->get();
    $this->assertCount(1, $users);
});

it('can delete records', function () {
    $user = DB::table('users')->insertGetId([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $this->assertIsString($user);

    $one_user = DB::table('users')->where('id', $user)->get();
    $this->assertCount(1, $one_user);

    DB::table('users')->where('id', $user)->delete();

    $no_users = DB::table('users')->where('id', $user)->get();
    $this->assertCount(0, $no_users);
});
