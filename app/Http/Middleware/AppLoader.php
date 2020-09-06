<?php

namespace App\Http\Middleware;

use App\Helpers\ThemesManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\View\FileViewFinder;

class AppLoader
{
    /**
     * @var Request|null
     */
    private $request = null;

    /**
     * @var ThemesManager|null
     */
    private $themesManager;

    public function __construct( Request $request )
    {
        $this->themesManager = ThemesManager::getInstance();
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
        if ( !did_action( 'contentpress/app/loaded' ) ) {
            do_action( 'contentpress/app/loaded' );
        }

        return $next( $request );
    }
}
