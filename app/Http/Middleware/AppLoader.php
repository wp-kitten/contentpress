<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppLoader
{
    /**
     * @var Request|null
     */
    private $request = null;

    public function __construct( Request $request )
    {
        $this->request = $request;
        if ( cp_is_admin() ) {
            if ( !did_action( 'contentpress/admin/init' ) ) {
                do_action( 'contentpress/admin/init' );
            }
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        //#! @1
        if ( !did_action( 'contentpress/app/loaded' ) ) {
            do_action( 'contentpress/app/loaded' );
        }

        //#! @2
        $paths = apply_filters( 'contentpress/register_view_paths', [] );
        $paths = array_merge( config( 'view.paths' ), $paths );
        //#! TODO: include path to teh currently active theme
//        $paths = array_merge( $paths, [ public_path( 'themes/THEME_NAME/views' ) ] );
        $paths = array_map( function ( $path ) {
            return wp_normalize_path( $path );
        }, $paths );
        $paths = array_unique( $paths );

        $viewFinder = app( 'view.finder' );
        $viewFinder->setPaths( $paths );

        return $next( $request );
    }
}
