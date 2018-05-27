<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Semux\Client\Configuration as SemuxClientConfig;
use Semux\Client\Api\SemuxApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        define('SEM_NANO', bcpow(10, 9));
        define('PREMINE_SEM', '25000000');
        define('DELEGATE_FEE_SEM', '1000');
        define('ADDRESS_AIRDROPS', "0x1504263ee17446ea5f8b288e1c35d05749c0e47d");
        define('ADDRESS_DEV', "0x541365fe0818ea0d2d7ab7f7bc79f719f5f72227");
        define('ADDRESS_FOUNDERS', "0xe30c510f3efc6e2bf98ff8f725548e6ece568f89");

        bcscale(9);

        $this->app->singleton(SemuxApi::class, function ($app) {
            $config = new SemuxClientConfig();
            $config
                ->setHost(env('SEMUX_API'))
                ->setUsername(env('SEMUX_API_USER'))
                ->setPassword(env('SEMUX_API_PASS'));
            $api = new SemuxApi(new \GuzzleHttp\Client(), $config);
            return $api;
        });
    }
}
