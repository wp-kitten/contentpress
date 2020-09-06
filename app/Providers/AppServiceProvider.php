<?php

namespace App\Providers;

use App\Helpers\Cache;
use App\Helpers\ContentPressCheckForUpdates;
use App\Helpers\CPML;
use App\Helpers\PluginsManager;
use App\Helpers\ThemesManager;
use App\Options;
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
        $this->app->bind( 'cp.updater', function ( $app ) {
            return new ContentPressCheckForUpdates();
        } );
        $this->app->bind( 'cp.cache', function ( $app ) {
            return new Cache( $app );
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

        PluginsManager::getInstance();
        ThemesManager::getInstance();

        if ( Schema::hasTable( 'options' ) ) {
            if ( cp_is_multilingual() ) {
                View::composer( '*', function ( $view ) {
                    $view->with( 'enabled_languages', ( new Options() )->getEnabledLanguages() );
                } );

                $locale = cp_get_user_meta( 'backend_user_current_language' );
                if ( empty( $locale ) ) {
                    $locale = CPML::getDefaultLanguageCode();
                }
                app()->setLocale( $locale );
            }
        }
    }
}
