<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Payment\Models\Entities\Bank;

$factory->define(Bank::class, function (Faker $faker) {
    return [
        'host_id'        => 1,
        'swift_id'       => $faker->isbn10,
        'bank_id'        => $faker->isbn10,
        'branch_id'      => $faker->isbn10,
        'account_number' => $faker->isbn10,
        'account_name'   => $faker->username
    ];
});
