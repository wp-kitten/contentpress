<?php

namespace App\Providers;

use App\Helpers\Cache;
use App\Helpers\CheckForUpdates;
use App\Helpers\VPML;
use App\Helpers\Theme;
use App\Helpers\ThemesManager;
use App\Models\Options;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Retrieve the reference to the instance of the internal CheckForUpdates class
         * @return CheckForUpdates
         */
        $this->app->bind( 'cp.updater', function ( $app ) {
            return new CheckForUpdates();
        } );

        /**
         * Retrieve the reference to the instance of the internal Cache class
         * @return Cache
         */
        $this->app->bind( 'vp.cache', function ( $app ) {
            return new Cache( $app );
        } );

        /**
         * Retrieve the reference to the instance of the active theme
         * @return Theme
         */
        $this->app->bind( 'cp.theme', function ( $app ) {
            $tm = ThemesManager::getInstance();
            return $tm->getActiveTheme();
        } );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength( 191 );

        Paginator::useBootstrap();

        if ( Schema::hasTable( 'options' ) ) {
            if ( vp_is_multilingual() ) {
                $locale = vp_get_user_meta( 'backend_user_current_language' );
                if ( empty( $locale ) ) {
                    $locale = VPML::getDefaultLanguageCode();
                }
                app()->setLocale( $locale );
            }
        }
    }
}
