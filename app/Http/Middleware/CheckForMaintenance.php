<?php

namespace App\Http\Middleware;

use Closure;

class CheckForMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        if ( !cp_is_under_maintenance() ) {
            return $next( $request );
        }

        //#! Allow if login
        if ( $request->path() == 'login' ) {
            return $next( $request );
        }

        //#! Allow administrators
        if ( vp_current_user_can( 'administrator' ) ) {
            return $next( $request );
        }

        return redirect()->route( 'app.maintenance' );
    }
}
