<?php

namespace WalkerChiu\Payment\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class PaymentFormRequest extends FormRequest
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
        $attributes = [
            'host_type'   => trans('php-payment::payment.host_type'),
            'host_id'     => trans('php-payment::payment.host_id'),
            'serial'      => trans('php-payment::payment.serial'),
            'type'        => trans('php-payment::payment.type'),
            'order'       => trans('php-payment::payment.order'),
            'options'     => trans('php-payment::payment.options'),
            'is_enabled'  => trans('php-payment::payment.is_enabled'),

            'name'        => trans('php-payment::payment.name'),
            'description' => trans('php-payment::payment.description'),
            'note'        => trans('php-payment::payment.note'),
            'remarks'     => trans('php-payment::payment.remarks')
        ];

        $request = Request::instance();
        switch ($request->type) {
            case "bank":
                $attributes = array_merge($attributes, [
                    'swift_id'       => trans('php-payment::bank.swift_id'),
                    'bank_id'        => trans('php-payment::bank.bank_id'),
                    'branch_id'      => trans('php-payment::bank.branch_id'),
                    'account_number' => trans('php-payment::bank.account_number'),
                    'account_name'   => trans('php-payment::bank.account_name'),

                    'bank_name'      => trans('php-payment::bank.bank_name'),
                    'branch_name'    => trans('php-payment::bank.branch_name')
                ]);
                break;
            case "cash":
                break;
            case "paypal":
                $attributes = array_merge($attributes, [
                    'username'      => trans('php-payment::paypal.username'),
                    'password'      => trans('php-payment::paypal.password'),
                    'client_id'     => trans('php-payment::paypal.client_id'),
                    'client_secret' => trans('php-payment::paypal.client_secret'),
                    'url_cancel'    => trans('php-payment::paypal.url_cancel'),
                    'url_return'    => trans('php-payment::paypal.url_return'),
                    'currency'      => trans('php-payment::paypal.currency'),
                    'locale'        => trans('php-payment::paypal.locale'),
                    'intent'        => trans('php-payment::paypal.intent')
                ]);
                break;
        }

        return $attributes;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'   => 'required_with:host_id|string',
            'host_id'     => 'required_with:host_type|integer|min:1',
            'serial'      => '',
            'type'        => '',
            'order'       => 'nullable|numeric|min:0',
            'options'     => 'nullable|json',
            'is_enabled'  => 'required|boolean',

            'name'        => 'required|string|max:255',
            'description' => '',
            'note'        => '',
            'remarks'     => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.payment.paypal').',id']]);
        }
        switch ($request->type) {
            case "bank":
                $rules = array_merge($rules, [
                    'swift_id'       => 'nullable|string|min:2|max:15',
                    'bank_id'        => 'required|string|min:2|max:10',
                    'branch_id'      => 'nullable|string|min:2|max:10',
                    'account_number' => 'required|string|min:6|max:20',
                    'account_name'   => 'required|string|min:6|max:20',

                    'bank_name'      => 'required|string|min:3',
                    'branch_name'    => 'nullable|string|min:3'
                ]);
                break;
            case "paypal":
                $rules = array_merge($rules, [
                    'username'      => 'required|string|min:2|max:255',
                    'password'      => 'required|string|min:6|max:255',
                    'client_id'     => 'required|string',
                    'client_secret' => 'required|string',
                    'url_cancel'    => 'url',
                    'url_return'    => 'url',
                    'currency'      => 'required|string',
                    'locale'        => ['required', Rule::in(config('wk-core.class.core.language')::getCodes())],
                    'intent'        => 'required|string'
                ]);
                break;
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
        $messages = [
            'id.required'              => trans('php-core::validation.required'),
            'id.integer'               => trans('php-core::validation.integer'),
            'id.min'                   => trans('php-core::validation.min'),
            'id.exists'                => trans('php-core::validation.exists'),
            'host_type.required_with'  => trans('php-core::validation.required_with'),
            'host_type.string'         => trans('php-core::validation.string'),
            'host_id.required_with'    => trans('php-core::validation.required_with'),
            'host_id.integer'          => trans('php-core::validation.integer'),
            'host_id.min'              => trans('php-core::validation.min'),
            'order.numeric'            => trans('php-core::validation.numeric'),
            'order.min'                => trans('php-core::validation.min'),
            'options.json'             => trans('php-core::validation.json'),
            'is_enabled.required'      => trans('php-core::validation.required'),
            'is_enabled.boolean'       => trans('php-core::validation.boolean'),

            'name.required'            => trans('php-core::validation.required'),
            'name.string'              => trans('php-core::validation.string'),
            'name.max'                 => trans('php-core::validation.max')
        ];

        $request = Request::instance();
        switch ($request->type) {
            case "bank":
                $messages = array_merge($messages, [
                    'swift_id.string'         => trans('php-core::validation.string'),
                    'swift_id.min'            => trans('php-core::validation.min'),
                    'swift_id.max'            => trans('php-core::validation.max'),
                    'bank_id.required'        => trans('php-core::validation.required'),
                    'bank_id.string'          => trans('php-core::validation.string'),
                    'bank_id.min'             => trans('php-core::validation.min'),
                    'bank_id.max'             => trans('php-core::validation.max'),
                    'branch_id.string'        => trans('php-core::validation.string'),
                    'branch_id.min'           => trans('php-core::validation.min'),
                    'branch_id.max'           => trans('php-core::validation.max'),
                    'account_number.required' => trans('php-core::validation.required'),
                    'account_number.string'   => trans('php-core::validation.string'),
                    'account_number.min'      => trans('php-core::validation.min'),
                    'account_number.max'      => trans('php-core::validation.max'),
                    'account_name.required'   => trans('php-core::validation.required'),
                    'account_name.string'     => trans('php-core::validation.string'),
                    'account_name.min'        => trans('php-core::validation.min'),
                    'account_name.max'        => trans('php-core::validation.max'),

                    'bank_name.required'      => trans('php-core::validation.required'),
                    'bank_name.string'        => trans('php-core::validation.string'),
                    'bank_name.min'           => trans('php-core::validation.min'),
                    'branch_name.string'      => trans('php-core::validation.string'),
                    'branch_name.min'         => trans('php-core::validation.min')
                ]);
                break;
            case "paypal":
                $messages = array_merge($messages, [
                    'username.required'      => trans('php-core::validation.required'),
                    'username.string'        => trans('php-core::validation.string'),
                    'username.min'           => trans('php-core::validation.min'),
                    'username.max'           => trans('php-core::validation.max'),
                    'password.required'      => trans('php-core::validation.required'),
                    'password.string'        => trans('php-core::validation.string'),
                    'password.min'           => trans('php-core::validation.min'),
                    'password.max'           => trans('php-core::validation.max'),
                    'client_id.required'     => trans('php-core::validation.required'),
                    'client_id.string'       => trans('php-core::validation.string'),
                    'client_secret.required' => trans('php-core::validation.required'),
                    'client_secret.string'   => trans('php-core::validation.string'),
                    'url_cancel.url'         => trans('php-core::validation.url'),
                    'url_return.url'         => trans('php-core::validation.url'),
                    'currency.required'      => trans('php-core::validation.required'),
                    'currency.string'        => trans('php-core::validation.string'),
                    'locale.required'        => trans('php-core::validation.required'),
                    'locale.in'              => trans('php-core::validation.in'),
                    'intent.required'        => trans('php-core::validation.required'),
                    'intent.string'          => trans('php-core::validation.string')
                ]);
                break;
        }

        return $messages;
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
