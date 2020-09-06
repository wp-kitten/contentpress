<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class ActiveUser
 * @package App\Http\Middleware
 *
 * Check the current user and logout + deny access if the user is blocked
 */
class ActiveUser
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
        $user = auth()->user();
        if ( $user && $user->is_blocked ) {
            auth()->logout();
            return redirect()
                ->route( 'login' )
                ->withError( __( 'a.Your account was blocked by an administrator.' ) );
        }

        return $next( $request );
    }
}
