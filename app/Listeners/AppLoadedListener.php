<?php

namespace App\Listeners;

use App\Events\AppLoadedEvent;
use App\Helpers\PluginsManager;
use App\Helpers\ThemesManager;

class AppLoadedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AppLoadedEvent $event
     * @return void
     */
    public function handle( AppLoadedEvent $event )
    {
        PluginsManager::getInstance();
        ThemesManager::getInstance();

        if ( cp_is_admin() ) {
            if ( !did_action( 'contentpress/admin/init' ) ) {
                do_action( 'contentpress/admin/init' );
            }
        }

        if ( !did_action( 'contentpress/app/loaded' ) ) {
            do_action( 'contentpress/app/loaded' );
        }
    }
}
