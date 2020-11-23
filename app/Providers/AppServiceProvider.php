<?php

namespace App\Providers;

use App\Helpers\Cache;
use App\Helpers\ContentPressCheckForUpdates;
use App\Helpers\CPML;
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
         * Retrieve the reference to the instance of the internal ContentPressCheckForUpdates class
         * @return ContentPressCheckForUpdates
         */
        $this->app->bind( 'cp.updater', function ( $app ) {
            return new ContentPressCheckForUpdates();
        } );

        /**
         * Retrieve the reference to the instance of the internal Cache class
         * @return Cache
         */
        $this->app->bind( 'cp.cache', function ( $app ) {
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
            if ( cp_is_multilingual() ) {
                $locale = cp_get_user_meta( 'backend_user_current_language' );
                if ( empty( $locale ) ) {
                    $locale = CPML::getDefaultLanguageCode();
                }
                app()->setLocale( $locale );
            }
        }
    }
}
