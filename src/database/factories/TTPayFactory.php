<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Payment\Models\Entities\TTPay;

$factory->define(TTPay::class, function (Faker $faker) {
    return [
        'host_id'    => 1,
        'apiKey'     => $faker->password,
        'secret'     => $faker->password,
        'storeCode'  => 'ST01',
        'tillId'     => '01',
        'ccy'        => $faker->currencyCode,
        'lang'       => $faker->randomElement(config('wk-core.class.core.language')::getCodes()),
        'url_return' => $faker->url
    ];
});
