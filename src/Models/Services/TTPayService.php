<?php

namespace WalkerChiu\Payment\Models\Services;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use WalkerChiu\Core\Models\Services\EnDecryptTrait;

class TTPayService
{
    use CheckExistTrait;
    use EnDecryptTrait;

    protected $setting;
    protected $base_url;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @param TTPay  $entity
     * @param Bool   $debug
     * @return void
     */
    public function __construct($entity, $debug = false)
    {
        $this->setting = [
            'apiKey'     => $entity->apiKey,
            'secret'     => $entity->secret,
            'sign'       => $this->HmacSHA1Base64Encrypt(time(), $entity->secret),
            'storeCode'  => $entity->storeCode,
            'tillId'     => $entity->tillId,
            'ccy'        => $entity->ccy,
            'lang'       => $entity->localeCode(),
            'salesman'   => $entity->salesman,
            'cashier'    => $entity->cashier,
            'url_return' => $entity->url_return,
            'timeout'    => $entity->timeout
        ];
        $this->base_url = $debug ?
            "https://pms-uat-fps.tech-trans.com/tt-pms-ws/rest" :
            "https://pms-uat-fps.tech-trans.com/tt-pms-ws/rest";

        $this->repository = App::make(config('wk-core.class.payment.ttpayRepository'));
    }

    /**
     * @param String  $apiKey
     * @return String
     */
    public function setApiKey(string $apiKey)
    {
        $this->setting['apiKey'] = $apiKey;

        return $this->setting['apiKey'];
    }

    /**
     * @param String  $secret
     * @return String
     */
    public function setSecret(string $secret)
    {
        $this->setting['secret'] = $secret;

        return $this->setting['secret'];
    }

    /**
     * @param String  $secret
     * @return String
     */
    public function setSign($secret = null)
    {
        $secret = $secret ?? $this->setting['secret'];

        $this->setting['sign'] = $this->HmacSHA1Base64Encrypt(time(), $secret);

        return $this->setting['sign'];
    }

    /**
     * @param String  $currency
     * @return String
     */
    public function setCurrency(string $currency)
    {
        $this->setting['ccy'] = $currency;

        return $this->setting['ccy'];
    }

    /**
     * @param String  $locale
     * @return String
     */
    public function setLocale(string $locale)
    {
        $this->setting['lang'] = $locale;

        return $this->setting['lang'];
    }

    /**
     * @return Mixed
     */
    public function gateway()
    {
        $request = HttpRequest::create($url, 'POST',
            [
                'apiKey'    => $this->setting['apiKey'],
                'sign'      => $this->setting['sign'],
                'timestamp' => $this->setting['timestamp'],
            ]
        );
        $request->headers->set('X-CSRF-TOKEN', csrf_token());

        return app()->handle($request);
    }

    /**
     * @param Array  $parameters
     * @return Mixed
     */
    public static function sales($parameters)
    {
        $request = HttpRequest::create($url, 'POST',
            [
                'apiKey'        => empty($parameters['apiKey']) ? $this->setting['apiKey'] : $parameters['apiKey'],
                'type'          => $parameters['type'],
                'rrn'           => $parameters['rrn'],
                'storeCode'     => empty($parameters['storeCode']) ? $this->setting['storeCode'] : $parameters['storeCode'],
                'tillId'        => empty($parameters['tillId']) ? $this->setting['tillId'] : $parameters['tillId'],
                'salesman'      => empty($parameters['salesman']) ? $this->setting['salesman'] : $parameters['salesman'],
                'cashier'       => empty($parameters['cashier']) ? $this->setting['cashier'] : $parameters['cashier'],
                'txDateTime'    => $parameters['txDateTime'],
                'amt'           => $parameters['amt'],
                'additionalAmt' => $parameters['additionalAmt'],
                'ccy'           => empty($parameters['ccy']) ? $this->setting['ccy'] : $parameters['ccy'],
                'remarks'       => $parameters['remarks'],
                'extendParam'   => $parameters['extendParam'],
                'lang'          => empty($parameters['lang']) ? $this->setting['lang'] : $parameters['lang'],
                'returnUrl'     => empty($parameters['url_return']) ? $this->setting['url_return'] : $parameters['url_return'],
                'sign'          => $this->setting['sign'],
                'timestamp'     => time(),
                'timeout'       => empty($parameters['timeout']) ? $this->setting['timeout'] : $parameters['timeout']
            ]
        );

        return app()->handle($request);
    }

    /**
     * @param Array  $parameters
     * @return Mixed
     */
    public static function query($parameters)
    {
        $request = HttpRequest::create($url, 'POST',
            [
                'apiKey'        => empty($parameters['apiKey']) ? $this->setting['apiKey'] : $parameters['apiKey'],
                'type'          => $parameters['type'],
                'rrn'           => $parameters['rrn'],
                'storeCode'     => empty($parameters['storeCode']) ? $this->setting['storeCode'] : $parameters['storeCode'],
                'tillId'        => empty($parameters['tillId']) ? $this->setting['tillId'] : $parameters['tillId'],
                'txDateTime'    => $parameters['txDateTime'],
                'amt'           => $parameters['amt'],
                'additionalAmt' => $parameters['additionalAmt'],
                'ccy'           => empty($parameters['ccy']) ? $this->setting['ccy'] : $parameters['ccy'],
                'extendParam'   => $parameters['extendParam'],
                'lang'          => empty($parameters['lang']) ? $this->setting['lang'] : $parameters['lang'],
                'sign'          => $this->setting['sign'],
                'timestamp'     => time(),
                'salesman'      => empty($parameters['salesman']) ? $this->setting['salesman'] : $parameters['salesman'],
                'cashier'       => empty($parameters['cashier']) ? $this->setting['cashier'] : $parameters['cashier']
            ]
        );

        return app()->handle($request);
    }

    /**
     * @param Array  $parameters
     * @return Mixed
     */
    public static function cancel($parameters)
    {
        $request = HttpRequest::create($url, 'POST',
            [
                'apiKey'      => empty($parameters['apiKey']) ? $this->setting['apiKey'] : $parameters['apiKey'],
                'type'        => $parameters['type'],
                'rrn'         => $parameters['rrn'],
                'storeCode'   => empty($parameters['storeCode']) ? $this->setting['storeCode'] : $parameters['storeCode'],
                'tillId'      => empty($parameters['tillId']) ? $this->setting['tillId'] : $parameters['tillId'],
                'extendParam' => $parameters['extendParam'],
                'lang'        => empty($parameters['lang']) ? $this->setting['lang'] : $parameters['lang'],
                'sign'        => $this->setting['sign'],
                'timestamp'   => time()
            ]
        );

        return app()->handle($request);
    }
}
