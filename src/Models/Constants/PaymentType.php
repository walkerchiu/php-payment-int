<?php

namespace WalkerChiu\Payment\Models\Constants;

/**
 * @license MIT
 * @package WalkerChiu\Payment
 * 
 * 
 */

class PaymentType
{
    /**
     * @return Array
     */
    public static function getCodes(): array
    {
        $items = [];
        $types = self::all();
        foreach ($types as $code => $type) {
            array_push($items, $code);
        }

        return $items;
    }

    /**
     * @param Bool  $onlyVaild
     * @return Array
     */
    public static function options($onlyVaild = false): array
    {
        $items = $onlyVaild ? [] : ['' => trans('php-core::system.null')];

        $types = self::all();
        foreach ($types as $key => $value) {
            $items = array_merge($items, [$key => trans('php-payment::constants.payment.'.$key)]);
        }

        return $items;
    }

    /**
     * @return Array
     */
    public static function all(): array
    {
        return [
            'applePay'  => 'Apple Pay',
            'aliPay'    => 'ALIPAY',
            'bank'      => 'Bank',
            'bill'      => 'Billing to mobile phones and landlines',
            'cash'      => 'Cash',
            'credit'    => 'Credit Card',
            'delivered' => 'Cash on Delivery',
            'debit'     => 'Debit Card',
            'ecpay'     => 'ECPay',
            'esafe'     => 'ESafe',
            'eMoney'    => 'Electronic Money',
            'ezPay'     => 'ezPay',
            'giftCard'  => 'Gift Card',
            'googlePay' => 'Google Pay',
            'linePay'   => 'Line Pay',
            'newebPay'  => 'NewebPay',
            'paypal'    => 'PayPal',
            'point'     => 'Point',
            'recon'     => 'Cityline RECON',
            'ttPay'     => 'TT Pay'
        ];
    }
}
