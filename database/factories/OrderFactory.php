<?php

use Faker\Generator as Faker;

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'customer_id' => factory(App\User::class),
        'reference' => str_random(5)
    ];
});
