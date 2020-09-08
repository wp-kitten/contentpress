<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

class CheckForMaintenance
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
        if ( !cp_is_under_maintenance() ) {
            return $next( $request );
        }

        //#! Allow if login
        if ( $request->path() == 'login' ) {
            return $next( $request );
        }

        //#! Allow administrators
        if ( cp_get_current_user() && cp_current_user_can( 'administrator' ) ) {
            return $next( $request );
        }

        return redirect()->route( 'app.maintenance' );
    }
}
