<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('users')->truncate();
});

it('can create records', function () {
    $users = DB::table('users')->get();
    expect($users)->toHaveCount(0);

    DB::table('users')->insert([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $users = DB::table('users')->get();
    expect($users)->toHaveCount(1);
});

it ('can read records', function () {
    $user = DB::table('users')->insertGetId([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $users = DB::table('users')->get();

    expect($users)
        ->toHaveCount(1)
        ->and($users[0])->toMatchArray([
            'user' => [
                'name'  => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
        ]);
});

it('can read records with where clause', function () {
    $uuid1 = (string) Str::uuid();
    $uuid2 = (string) Str::uuid();

    $query = DB::table('users')->insert([
        [
            'user.uuid'  => $uuid1,
            'user.name'  => 'John Doe',
            'user.email' => 'john.doe@example.com'
        ],
        [
            'user.uuid'  => $uuid2,
            'user.name'  => 'Jane Doe',
            'user.email' => 'jane.doe@example.com',
        ],
    ]);

    $user = DB::table('users')
        ->where('user.uuid', $uuid2)
        ->get();

    expect($user)
        ->toHaveCount(1)
        ->and($user[0])->toMatchArray([
            'user' => [
                'uuid'  => $uuid2,
                'name'  => 'Jane Doe',
                'email' => 'jane.doe@example.com',
            ],
        ]);
});

it('can update records', function () {
    $user = DB::table('users')->insertGetId([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    $this->assertIsString($user);

    DB::table('users')
        ->where('user.name', 'John Doe')
        ->update([
            'user.name'  => 'Jane Doe',
        ]);

    $updated_user = DB::table('users')->where('id', $user)->first();
    expect($updated_user)
        ->toBeArray()
        ->toMatchArray([
            'user' => [
                'name'  => 'Jane Doe',
                'email' => 'john.doe@example.com',
            ],
        ]);
});

it('can delete records', function () {
    $user = DB::table('users')->insertGetId([
        'user.name'  => 'John Doe',
        'user.email' => 'john.doe@example.com',
    ]);

    expect($user)->toBeString();

    $one_user = DB::table('users')->where('id', $user)->get();
    expect($one_user)->toHaveCount(1);

    DB::table('users')->where('id', $user)->delete();

    $no_users = DB::table('users')->where('id', $user)->get();
    expect($no_users)->toHaveCount(0);
});
