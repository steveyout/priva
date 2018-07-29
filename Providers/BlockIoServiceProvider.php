<?php

namespace App\Providers;

use BlockIo;
use Illuminate\Support\ServiceProvider;

class BlockIoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('blockio', function () {
            return new BlockIo(config('blockio.api_key'), config('blockio.pin'), 2);
        });
    }
}
