<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class CheckForMaintenanceMode
 * @package App\Http\Middleware
 *
 * Check to see whether or not the application is under maintenance
 * and redirect unauthorized users to the maintenance page while allowing
 * administrators to access the website
 */
class CheckForMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        $underMaintenance = cp_is_under_maintenance();

        if ( !$underMaintenance ) {
            return $next( $request );
        }

        //#! Allow if login
        if ( $request->path() == 'login' ) {
            return $next( $request );
        }

        //#! Allow administrators
        if ( cp_current_user_can( 'manage_options' ) ) {
            return $next( $request );
        }

        return redirect()->route( 'app.maintenance' );
    }
}
