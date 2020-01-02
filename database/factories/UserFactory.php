<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$uxeipXQNVcTY2fwhYpHIMOvJsAj0CLaFPSIjUNzUGwq7jEp.jfmAi',
        'active' => 0,
    ];
});
