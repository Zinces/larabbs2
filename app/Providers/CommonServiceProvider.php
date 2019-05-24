<?php

namespace App\Providers;

use App\Http\Controllers\CommonController;
use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts._header','App\Http\Controllers\CommonController@topMenu');
    }
}
