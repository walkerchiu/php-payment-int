<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Payment\Models\Entities\ECPay;

$factory->define(ECPay::class, function (Faker $faker) {
    return [
        'merchant_id' => $faker->slug,
        'method'      => $faker->slug,
        'hash_key'    => $faker->slug,
        'hash_iv'     => $faker->slug
    ];
});
