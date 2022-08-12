<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Payment\Models\Entities\Payment;
use WalkerChiu\Payment\Models\Entities\PaymentLang;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'serial' => $faker->isbn10,
        'type'   => $faker->randomElement(config('wk-core.class.payment.paymentType')::getCodes())
    ];
});

$factory->define(PaymentLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence
    ];
});
