<?php

namespace WalkerChiu\Payment\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class PaymentService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.payment.paymentRepository'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @return Array
     */
    public function listForOrder(string $code, array $data)
    {
    	return $this->repository->listForOrder($code, $data);
    }
}
