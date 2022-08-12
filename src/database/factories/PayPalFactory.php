<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Payment\Models\Entities\PayPal;

$factory->define(PayPal::class, function (Faker $faker) {
    return [
        'host_id'       => 1,
        'username'      => $faker->username,
        'password'      => $faker->password,
        'client_id'     => $faker->slug,
        'client_secret' => $faker->password,
        'url_cancel'    => $faker->url,
        'url_return'    => $faker->url,
        'currency'      => $faker->currencyCode,
        'locale'        => $faker->randomElement(config('wk-core.class.core.language')::getCodes()),
        'intent'        => $faker->randomElement(['CAPTURE', 'AUTHORIZE'])
    ];
});
