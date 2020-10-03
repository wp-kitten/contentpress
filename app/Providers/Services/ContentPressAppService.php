<?php

namespace App\Providers\Services;

use App\Events\AppLoadedEvent;
use Illuminate\Support\ServiceProvider;

class ContentPressAppService extends ServiceProvider
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
        //#! Notify everyone the app has loaded
        //#! @see App\Listeners\AppLoadedListener
        event( new AppLoadedEvent() );
    }
}
