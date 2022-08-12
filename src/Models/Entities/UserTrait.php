<?php

namespace WalkerChiu\Payment\Models\Entities;

trait UserTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payments()
    {
        return $this->morphMany(config('wk-core.class.payment.payment'), 'morph');
    }
}
