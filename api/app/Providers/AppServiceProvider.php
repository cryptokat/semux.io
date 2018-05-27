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
        bcscale(9);

        $this->app->singleton(SemuxApi::class, function ($app) {
            $config = new SemuxClientConfig();
            $config->setHost('https://semux.io/api/semux/v2.1.0');
            $api = new SemuxApi(new \GuzzleHttp\Client(), $config);
            return $api;
        });
    }
}
