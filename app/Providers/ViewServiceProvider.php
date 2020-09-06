<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

/**
 * Class ViewServiceProvider
 * @package App\Providers
 *
 * Allows themes & plugins to register their own view directories
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * @uses apply_filters( 'contentpress/register_view_paths', [] );
         */
        $this->app->bind( 'view.finder', function ( $app ) {
            //#! Register dynamic view paths
            $paths = array_merge( $app[ 'config' ][ 'view.paths' ], [] );

            //#! Set registered paths before the ones set in app/config/views.php
            //#! Themes & plugins can use this filter to inject their own paths
            $dynamicPaths = apply_filters( 'contentpress/register_view_paths', [] );
            foreach ( $dynamicPaths as $path ) {
                array_unshift( $paths, $path );
            }

            $paths = array_map( function ( $path ) {
                return wp_normalize_path( $path );
            }, $paths );
            $paths = array_unique( $paths );

            return new FileViewFinder( $app[ 'files' ], $paths );
        } );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
