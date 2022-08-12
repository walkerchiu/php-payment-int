<?php

namespace WalkerChiu\Payment\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class Bank extends Entity
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
        $this->table = config('wk-core.table.payment.banks');

        $this->fillable = array_merge($this->fillable, [
            'host_id',
            'swift_id', 'bank_id', 'branch_id', 'account_number', 'account_name'
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
