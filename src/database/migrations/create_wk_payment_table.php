<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkPaymentTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.payment.payments'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('host');
            $table->string('serial')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('order')->nullable();
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('type');
            $table->index('is_enabled');
        });
        if (!config('wk-payment.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.payment.payments_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
        Schema::create(config('wk-core.table.payment.banks'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('host_id');
            $table->string('swift_id', 15)->nullable();
            $table->string('bank_id', 10);
            $table->string('branch_id', 10)->nullable();
            $table->string('account_number', 20);
            $table->string('account_name');

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('host_id')->references('id')
                  ->on(config('wk-core.table.payment.payments'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('swift_id');
            $table->index(['bank_id', 'branch_id']);
            $table->index('account_number');
        });
        Schema::create(config('wk-core.table.payment.ecpay'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('host_id');
            $table->string('merchant_id');
            $table->string('method');
            $table->string('hash_key');
            $table->string('hash_iv');
            $table->string('url_notify')->nullable();
            $table->string('url_return')->nullable();
            $table->string('hash_key_invoice')->nullable();
            $table->string('hash_iv_invoice')->nullable();

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('host_id')->references('id')
                  ->on(config('wk-core.table.payment.payments'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        Schema::create(config('wk-core.table.payment.paypal'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('host_id');
            $table->string('username');
            $table->string('password');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('url_cancel')->nullable();
            $table->string('url_return')->nullable();
            $table->string('currency');
            $table->string('locale', 5);
            $table->string('intent');

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('host_id')->references('id')
                  ->on(config('wk-core.table.payment.payments'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        Schema::create(config('wk-core.table.payment.ttpay'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('host_id');
            $table->string('apiKey');
            $table->string('secret');
            $table->string('storeCode');
            $table->string('tillId');
            $table->string('ccy')->nullable();
            $table->string('lang', 5)->nullable();
            $table->string('salesman')->nullable();
            $table->string('cashier')->nullable();
            $table->string('url_return')->nullable();
            $table->unsignedInteger('timeout')->default(300000);

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('host_id')->references('id')
                  ->on(config('wk-core.table.payment.payments'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.payment.ttpay'));
        Schema::dropIfExists(config('wk-core.table.payment.paypal'));
        Schema::dropIfExists(config('wk-core.table.payment.ecpay'));
        Schema::dropIfExists(config('wk-core.table.payment.banks'));
        Schema::dropIfExists(config('wk-core.table.payment.payments_lang'));
        Schema::dropIfExists(config('wk-core.table.payment.payments'));
    }
}
