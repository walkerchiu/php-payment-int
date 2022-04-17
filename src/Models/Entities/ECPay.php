<?php

namespace WalkerChiu\Payment\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class ECPay extends Entity
{
    use LangTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.payment.ecpay');

        $this->fillable = array_merge($this->fillable, [
            'host_id',
            'merchant_id', 'method',
            'hash_key', 'hash_iv',
            'url_notify', 'url_return',
            'hash_key_invoice', 'hash_iv_invoice'
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function host()
    {
        return $this->belongsTo(config('wk-core.class.payment.payment'), 'host_id', 'id');
    }
}
