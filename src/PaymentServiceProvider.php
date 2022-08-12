<?php

namespace WalkerChiu\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/payment.php' => config_path('wk-payment.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_payment_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_payment_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-payment');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-payment'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-payment.command.cleaner')
            ]);
        }

        config('wk-core.class.payment.payment')::observe(config('wk-core.class.payment.paymentObserver'));
        config('wk-core.class.payment.paymentLang')::observe(config('wk-core.class.payment.paymentLangObserver'));
        config('wk-core.class.payment.bank')::observe(config('wk-core.class.payment.bankObserver'));
        config('wk-core.class.payment.ecpay')::observe(config('wk-core.class.payment.ecpayObserver'));
        config('wk-core.class.payment.paypal')::observe(config('wk-core.class.payment.paypalObserver'));
        config('wk-core.class.payment.ttpay')::observe(config('wk-core.class.payment.ttpayObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-payment')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/payment.php', 'wk-payment'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/payment.php', 'payment'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
