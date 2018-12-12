<?php

use Faker\Generator as Faker;

$factory->define(App\File::class, function (Faker $faker) {
    return [
        'name' => str_random(5),
        'extension' => $faker->fileExtension,
        'size' => $faker->randomNumber(2),
        'created_by' => $faker->name,
        'updated_by' => $faker->name,
    ];
});
