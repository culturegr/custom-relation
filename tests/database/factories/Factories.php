<?php

use Faker\Generator as Faker;
use CultureGr\CustomRelation\Tests\Fixtures\User;
use CultureGr\CustomRelation\Tests\Fixtures\Role;
use CultureGr\CustomRelation\Tests\Fixtures\Permission;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt($faker->password),
    ];
});

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->define(Permission::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});