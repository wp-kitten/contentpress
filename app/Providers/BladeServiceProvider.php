<?php

namespace App\Providers;

use App\Helpers\ScriptsManager;
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
            ob_start();
            do_action( 'contentpress/site/head' );
            ScriptsManager::printStylesheets();
            ScriptsManager::printLocalizedScripts();
            ScriptsManager::printHeadScripts();
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        } );

        Blade::directive( 'cp_footer', function () {
            ob_start();
            do_action( 'contentpress/site/footer' );
            ScriptsManager::printFooterScripts();
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        } );
    }
}
