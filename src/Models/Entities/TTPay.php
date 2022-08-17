<?php

namespace WalkerChiu\Payment\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class TTPay extends Entity
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
        $this->table = config('wk-core.table.payment.ttpay');

        $this->fillable = array_merge($this->fillable, [
            'host_id',
            'apiKey', 'secret',
            'storeCode', 'tillId',
            'ccy', 'lang',
            'salesman', 'cashier',
            'url_return',
            'timeout',
            'options',
        ]);

        parent::__construct($attributes);
    }

    public function localeCode()
    {
        $items = explode('_', $this->attributes['locale']);

        return $items[0] .'_'. strtoupper($items[1]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function host()
    {
        return $this->belongsTo(config('wk-core.class.payment.payment'), 'host_id', 'id');
    }
}
