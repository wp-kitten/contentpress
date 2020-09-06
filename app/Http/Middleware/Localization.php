<?php

namespace App\Http\Middleware;

use App\Settings;
use Closure;
use Illuminate\Support\Facades\App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //#! Frontend
        if ( false !== stripos( $request->route()->getName(), 'app.' ) ) {
            if ( session()->has( 'frontend_user_language_code' ) ) {
                App::setLocale( session()->get( 'frontend_user_language_code' ) );
            }
            else {
                App::setLocale( ( new Settings() )->getSetting('default_language') );
            }
        }
        //#! Backend
        elseif ( false !== stripos( $request->route()->getName(), 'admin.' ) ) {
            if ( session()->has( 'backend_user_current_language' ) ) {
                App::setLocale( session()->get( 'backend_user_current_language' ) );
            }
            else {
                App::setLocale( ( new Settings() )->getSetting('default_language') );
            }
        }

        return $next( $request );
    }
}
