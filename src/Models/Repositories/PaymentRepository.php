<?php

namespace WalkerChiu\Payment\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class PaymentRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.payment.payment'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                          ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['host_type']), function ($query) use ($data) {
                                                return $query->where('host_type', $data['host_type']);
                                            })
                                            ->unless(empty($data['host_id']), function ($query) use ($data) {
                                                return $query->where('host_id', $data['host_id']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['type']), function ($query) use ($data) {
                                                return $query->where('type', $data['type']);
                                            })
                                            ->unless(empty($data['swift_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('swift_id', $data['swift_id']);
                                                });
                                            })
                                            ->unless(empty($data['bank_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('bank_id', $data['bank_id']);
                                                });
                                            })
                                            ->unless(empty($data['branch_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('branch_id', $data['branch_id']);
                                                });
                                            })
                                            ->unless(empty($data['account_number']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('account_number', 'LIKE', $data['account_number']."%");
                                                });
                                            })
                                            ->unless(empty($data['account_name']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('account_name', 'LIKE', $data['account_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['bank_name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'bank_name')
                                                          ->where('value', 'LIKE', "%".$data['bank_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['branch_name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'branch_name')
                                                          ->where('value', 'LIKE', "%".$data['branch_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'name')
                                                          ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'description')
                                                          ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            })
                                            ->unless(empty($data['note']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'note')
                                                          ->where('value', 'LIKE', "%".$data['note']."%");
                                                });
                                            })
                                            ->unless(empty($data['remarks']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'remarks')
                                                          ->where('value', 'LIKE', "%".$data['remarks']."%");
                                                });
                                            });
                                })
                                ->orderBy('order', 'ASC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-payment.output_format'), config('wk-payment.pagination.pageName'), config('wk-payment.pagination.perPage'));
            $factory->setFieldsLang(['name', 'description', 'note', 'remarks']);
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @return Array
     */
    public function listForOrder(string $code, array $data): array
    {
        $instance = $this->instance->ofEnabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                        ->ofCode($code);
                                }])
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['host_type']), function ($query) use ($data) {
                                                return $query->where('host_type', $data['host_type']);
                                            })
                                            ->unless(empty($data['host_id']), function ($query) use ($data) {
                                                return $query->where('host_id', $data['host_id']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['type']), function ($query) use ($data) {
                                                return $query->where('type', $data['type']);
                                            })
                                            ->unless(empty($data['swift_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('swift_id', $data['swift_id']);
                                                });
                                            })
                                            ->unless(empty($data['bank_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('bank_id', $data['bank_id']);
                                                });
                                            })
                                            ->unless(empty($data['branch_id']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('branch_id', $data['branch_id']);
                                                });
                                            })
                                            ->unless(empty($data['account_number']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('account_number', 'LIKE', $data['account_number']."%");
                                                });
                                            })
                                            ->unless(empty($data['account_name']), function ($query) use ($data) {
                                                return $query->whereHas('bank', function ($query) use ($data) {
                                                    $query->where('account_name', 'LIKE', $data['account_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['bank_name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'bank_name')
                                                        ->where('value', 'LIKE', "%".$data['bank_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['branch_name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'branch_name')
                                                        ->where('value', 'LIKE', "%".$data['branch_name']."%");
                                                });
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'name')
                                                        ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'description')
                                                        ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            })
                                            ->unless(empty($data['note']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'note')
                                                        ->where('value', 'LIKE', "%".$data['note']."%");
                                                });
                                            })
                                            ->unless(empty($data['remarks']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'remarks')
                                                        ->where('value', 'LIKE', "%".$data['remarks']."%");
                                                });
                                            });
                                })
                                ->orderBy('order', 'ASC')
                                ->get();
        $list = [];
        foreach ($records as $record) {
            if (!isset($list[$record->type]))
                $list = array_merge($list, [$record->type => []]);

            if ($record->type == 'bank') {
                array_push($list[$record->type], [
                    'id'             => $record->id,
                    'type'           => $record->type,
                    'order'          => $record->order,
                    'swift_id'       => $record->swift_id,
                    'bank_id'        => $record->bank_id,
                    'branch_id'      => $record->branch_id,
                    'account_number' => $record->account_number,
                    'account_name'   => $record->account_name,
                    'bank_name'      => $record->findLangByKey('bank_name'),
                    'branch_name'    => $record->findLangByKey('branch_name')
                ]);
            } elseif ($record->type == 'paypal') {
                array_push($list[$record->type], [
                    'id'          => $record->id,
                    'type'        => $record->type,
                    'order'       => $record->order,
                    'name'        => $record->findLangByKey('name'),
                    'description' => $record->findLangByKey('description'),
                    'note'        => $record->findLangByKey('note')
                ]);
            }
        }

        return $list;
    }

    /**
     * @param Payment       $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
        $data = [
            'id' => $instance ? $instance->id : '',
            'constant' => [
                'payment'  => config('wk-core.class.payment.paymentType')::options(true)
            ],
            'basic' => []
        ];

        if (empty($instance))
            return $data;

        $this->setEntity($instance);

        if (is_string($code)) {
            $data['basic'] = [
                'host_type'      => $instance->host_type,
                'host_id'        => $instance->host_id,
                'serial'         => $instance->serial,
                'type'           => $instance->type,
                'order'          => $instance->order,
                'options'        => $instance->options,
                'name'           => $instance->findLang($code, 'name'),
                'description'    => $instance->findLang($code, 'description'),
                'note'           => $instance->findLang($code, 'note'),
                'remarks'        => $instance->findLang($code, 'remarks'),
                'is_enabled'     => $instance->is_enabled,
                'updated_at'     => $instance->updated_at
            ];
            if ($instance->bank) {
                $data['basic']['bank'] = [
                    'swift_id'       => $instance->bank->swift_id,
                    'bank_id'        => $instance->bank->bank_id,
                    'branch_id'      => $instance->bank->branch_id,
                    'account_number' => $instance->bank->account_number,
                    'account_name'   => $instance->bank->account_name,
                    'bank_name'      => $instance->findLang($code, 'bank_name'),
                    'branch_name'    => $instance->findLang($code, 'branch_name')
                ];
            } elseif ($instance->paypal) {
                $data['basic']['paypal'] = [
                    'username'      => $instance->paypal->username,
                    'password'      => $instance->paypal->password,
                    'client_id'     => $instance->paypal->client_id,
                    'client_secret' => $instance->paypal->client_secret,
                    'callback_url'  => $instance->paypal->callback_url,
                    'currency'      => $instance->paypal->currency,
                    'locale'        => $instance->paypal->locale,
                    'validate_ssl'  => $instance->paypal->validate_ssl,
                    'intent'        => $instance->paypal->intent
                ];
            }

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                    'host_type'      => $instance->host_type,
                    'host_id'        => $instance->host_id,
                    'serial'         => $instance->serial,
                    'type'           => $instance->type,
                    'order'          => $instance->order,
                    'options'        => $instance->options,
                    'name'           => $instance->findLang($language, 'name'),
                    'description'    => $instance->findLang($language, 'description'),
                    'note'           => $instance->findLang($language, 'note'),
                    'remarks'        => $instance->findLang($language, 'remarks'),
                    'is_enabled'     => $instance->is_enabled,
                    'updated_at'     => $instance->updated_at
                ];
                if ($instance->bank) {
                    $data['basic'][$language]['bank'] = [
                        'swift_id'       => $instance->bank->swift_id,
                        'bank_id'        => $instance->bank->bank_id,
                        'branch_id'      => $instance->bank->branch_id,
                        'account_number' => $instance->bank->account_number,
                        'account_name'   => $instance->bank->account_name,
                        'bank_name'      => $instance->findLang($language, 'bank_name'),
                        'branch_name'    => $instance->findLang($language, 'branch_name')
                    ];
                } elseif ($instance->paypal) {
                    $data['basic'][$language]['paypal'] = [
                        'username'      => $instance->paypal->username,
                        'password'      => $instance->paypal->password,
                        'client_id'     => $instance->paypal->client_id,
                        'client_secret' => $instance->paypal->client_secret,
                        'callback_url'  => $instance->paypal->callback_url,
                        'currency'      => $instance->paypal->currency,
                        'locale'        => $instance->paypal->locale,
                        'validate_ssl'  => $instance->paypal->validate_ssl,
                        'intent'        => $instance->paypal->intent
                    ];
                }
            }
        }

        return $data;
    }
}
