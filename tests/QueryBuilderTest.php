<?php

declare(strict_types=1);

use BooneStudios\Surreal\Schema\Blueprint;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

afterAll(function () {
    Schema::drop('testdb');
});

it('can create a new table', function () {
    Schema::create('testdb', function (BaseBlueprint $table) {
        $table->index();
        $table->string('name');
        $table->timestamps();
    });

    expect(Schema::hasTable('testdb'))->toBeTrue();
});
