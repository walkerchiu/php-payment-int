<?php

namespace WalkerChiu\Payment\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class TTPayRepository extends Repository
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
        $this->instance = App::make(config('wk-core.class.payment.ttpay'));
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
                                            ->unless(empty($data['apiKey']), function ($query) use ($data) {
                                                return $query->where('apiKey', $data['apiKey']);
                                            })
                                            ->unless(empty($data['storeCode']), function ($query) use ($data) {
                                                return $query->where('storeCode', $data['storeCode']);
                                            })
                                            ->unless(empty($data['tillId']), function ($query) use ($data) {
                                                return $query->where('tillId', $data['tillId']);
                                            })
                                            ->unless(empty($data['ccy']), function ($query) use ($data) {
                                                return $query->where('ccy', $data['ccy']);
                                            })
                                            ->unless(empty($data['lang']), function ($query) use ($data) {
                                                return $query->where('lang', $data['lang']);
                                            })
                                            ->unless(empty($data['salesman']), function ($query) use ($data) {
                                                return $query->where('salesman', $data['salesman']);
                                            })
                                            ->unless(empty($data['cashier']), function ($query) use ($data) {
                                                return $query->where('cashier', $data['cashier']);
                                            })
                                            ->unless(empty($data['timeout']), function ($query) use ($data) {
                                                return $query->where('timeout', $data['timeout']);
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
     * @param TTPay         $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
    }
}
