<?php

namespace WalkerChiu\Payment\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class BankRepository extends Repository
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
        $this->instance = App::make(config('wk-core.class.payment.bank'));
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
                                            ->unless(empty($data['swift_id']), function ($query) use ($data) {
                                                return $query->where('swift_id', $data['swift_id']);
                                            })
                                            ->unless(empty($data['bank_id']), function ($query) use ($data) {
                                                return $query->where('bank_id', $data['bank_id']);
                                            })
                                            ->unless(empty($data['branch_id']), function ($query) use ($data) {
                                                return $query->where('branch_id', $data['branch_id']);
                                            })
                                            ->unless(empty($data['account_number']), function ($query) use ($data) {
                                                return $query->where('account_number', $data['account_number']);
                                            })
                                            ->unless(empty($data['account_name']), function ($query) use ($data) {
                                                return $query->where('account_name', $data['account_name']);
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-payment.output_format'), config('wk-payment.pagination.pageName'), config('wk-payment.pagination.perPage'));
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Bank          $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
    }
}
