<?php

namespace WalkerChiu\Payment\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class PaymentPayPalFormRequest extends FormRequest
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

            'username'      => trans('php-payment::paypal.username'),
            'password'      => trans('php-payment::paypal.password'),
            'client_id'     => trans('php-payment::paypal.client_id'),
            'client_secret' => trans('php-payment::paypal.client_secret'),
            'url_cancel'    => trans('php-payment::paypal.url_cancel'),
            'url_return'    => trans('php-payment::paypal.url_return'),
            'currency'      => trans('php-payment::paypal.currency'),
            'locale'        => trans('php-payment::paypal.locale'),
            'intent'        => trans('php-payment::paypal.intent'),

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

            'username'      => 'required|string|min:2|max:255',
            'password'      => 'required|string|min:6|max:255',
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
            'url_cancel'    => 'url',
            'url_return'    => 'url',
            'currency'      => 'required|string',
            'locale'        => ['required', Rule::in(config('wk-core.class.core.language')::getCodes())],
            'intent'        => 'required|string',

            'name'        => 'required|string|max:255',
            'description' => '',
            'note'        => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.payment.paypal').',id']]);
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
            'intent.string'          => trans('php-core::validation.string'),

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
