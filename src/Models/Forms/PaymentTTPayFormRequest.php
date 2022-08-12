<?php

namespace WalkerChiu\Payment\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class PaymentTTPayFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'  => trans('php-payment::payment.host_type'),
            'host_id'    => trans('php-payment::payment.host_id'),
            'serial'     => trans('php-payment::payment.serial'),
            'type'       => trans('php-payment::payment.type'),
            'order'      => trans('php-payment::payment.order'),
            'is_enabled' => trans('php-payment::payment.is_enabled'),

            'apiKey'     => trans('php-payment::ttpay.apiKey'),
            'secret'     => trans('php-payment::ttpay.secret'),
            'storeCode'  => trans('php-payment::ttpay.storeCode'),
            'tillId'     => trans('php-payment::ttpay.tillId'),
            'ccy'        => trans('php-payment::ttpay.ccy'),
            'lang'       => trans('php-payment::ttpay.lang'),
            'salesman'   => trans('php-payment::ttpay.salesman'),
            'cashier'    => trans('php-payment::ttpay.cashier'),
            'url_return' => trans('php-payment::ttpay.url_return'),
            'timeout'    => trans('php-payment::ttpay.timeout'),

            'name'        => trans('php-payment::payment.name'),
            'description' => trans('php-payment::payment.description'),
            'note'        => trans('php-payment::payment.note')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'  => 'required_with:host_id|string',
            'host_id'    => 'required_with:host_type|integer|min:1',
            'serial'     => '',
            'type'       => '',
            'order'      => 'nullable|numeric|min:0',
            'is_enabled' => 'boolean',

            'apiKey'     => 'required|string',
            'secret'     => 'required|string',
            'storeCode'  => 'required|string',
            'tillId'     => 'required|string',
            'ccy'        => 'nullable|string',
            'lang'       => 'nullable|string',
            'salesman'   => 'nullable|string',
            'cashier'    => 'nullable|string',
            'url_return' => 'nullable|url',
            'timeout'    => 'nullable|integer|min:300000',

            'name'        => 'required|string|max:255',
            'description' => '',
            'note'        => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.payment.ttpay').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.integer'              => trans('php-core::validation.integer'),
            'id.min'                  => trans('php-core::validation.min'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.integer'         => trans('php-core::validation.integer'),
            'host_id.min'             => trans('php-core::validation.min'),
            'order.numeric'           => trans('php-core::validation.numeric'),
            'order.min'               => trans('php-core::validation.min'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'apiKey.required'    => trans('php-core::validation.required'),
            'apiKey.string'      => trans('php-core::validation.string'),
            'secret.required'    => trans('php-core::validation.required'),
            'secret.string'      => trans('php-core::validation.string'),
            'storeCode.required' => trans('php-core::validation.required'),
            'storeCode.string'   => trans('php-core::validation.string'),
            'tillId.required'    => trans('php-core::validation.required'),
            'tillId.string'      => trans('php-core::validation.string'),
            'ccy.string'         => trans('php-core::validation.string'),
            'lang.string'        => trans('php-core::validation.string'),
            'salesman.string'    => trans('php-core::validation.string'),
            'cashier.string'     => trans('php-core::validation.string'),
            'url_return.url'     => trans('php-core::validation.url'),
            'timeout.integer'    => trans('php-core::validation.integer'),
            'timeout.min'        => trans('php-core::validation.min'),

            'name.required' => trans('php-core::validation.required'),
            'name.string'   => trans('php-core::validation.string'),
            'name.max'      => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-payment.onoff.site')
                    && !empty(config('wk-core.class.site.site'))
                    && $data['host_type'] == config('wk-core.class.site.site')
                ) {
                    $result = DB::table(config('wk-core.table.site.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-payment.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['host_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-payment.onoff.account')
                    && !empty(config('wk-core.class.account.profile'))
                    && $data['host_type'] == config('wk-core.class.account.profile')
                ) {
                    $result = DB::table(config('wk-core.table.account.profiles'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    !empty(config('wk-core.class.user'))
                    && $data['host_type'] == config('wk-core.class.user')
                ) {
                    $result = DB::table(config('wk-core.table.user'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
        });
    }
}
