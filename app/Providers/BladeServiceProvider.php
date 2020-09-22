<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
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
        Blade::directive( 'cp_head', function () {
            return contentpressHead();
        } );

        Blade::directive( 'cp_footer', function () {
            return contentpressFooter();
        } );
    }
}
