<?php

namespace WalkerChiu\Payment\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class PaymentECPayFormRequest extends FormRequest
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
            'options'    => trans('php-payment::payment.options'),
            'is_enabled' => trans('php-payment::payment.is_enabled'),

            'merchant_id'      => trans('php-payment::ecpay.merchant_id'),
            'method'           => trans('php-payment::ecpay.method'),
            'hash_key'         => trans('php-payment::ecpay.hash_key'),
            'hash_iv'          => trans('php-payment::ecpay.hash_iv'),
            'url_notify'       => trans('php-payment::ecpay.url_notify'),
            'url_return'       => trans('php-payment::ecpay.url_return'),
            'hash_key_invoice' => trans('php-payment::ecpay.hash_key_invoice'),
            'hash_iv_invoice'  => trans('php-payment::ecpay.hash_iv_invoice'),

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
            'options'    => 'nullable|json',
            'is_enabled' => 'boolean',

            'merchant_id'      => 'required|string',
            'method'           => 'required|string',
            'hash_key'         => 'required|string',
            'hash_iv'          => 'required|string',
            'url_notify'       => 'url',
            'url_return'       => 'url',
            'hash_key_invoice' => 'string',
            'hash_iv_invoice'  => 'string',

            'name'        => 'required|string|max:255',
            'description' => '',
            'note'        => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.payment.ecpay').',id']]);
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
            'options.json'            => trans('php-core::validation.json'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'merchant_id.required'    => trans('php-core::validation.required'),
            'merchant_id.string'      => trans('php-core::validation.string'),
            'method.required'         => trans('php-core::validation.required'),
            'method.string'           => trans('php-core::validation.string'),
            'hash_key.required'       => trans('php-core::validation.required'),
            'hash_key.string'         => trans('php-core::validation.string'),
            'hash_iv.required'        => trans('php-core::validation.required'),
            'hash_iv.string'          => trans('php-core::validation.string'),
            'url_notify.url'          => trans('php-core::validation.url'),
            'url_return.url'          => trans('php-core::validation.url'),
            'hash_key_invoice.string' => trans('php-core::validation.string'),
            'hash_iv_invoice.string'  => trans('php-core::validation.string'),

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
                    config('wk-payment.onoff.site-mall')
                    && !empty(config('wk-core.class.site-mall.site'))
                    && $data['host_type'] == config('wk-core.class.site-mall.site')
                ) {
                    $result = DB::table(config('wk-core.table.site-mall.sites'))
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
