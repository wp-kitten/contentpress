<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanAccessAdmin
{
    /**
     * Ensure the current user can access any of the admin areas.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @hooked valpress/admin_access/forbidden/redirect_route
     * @hooked valpress/admin_access/allowed
     * @hooked valpress/admin_access/forbidden/error_message
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next )
    {
        //#! Let ajax requests pass
        if ( $request->ajax() ) {
            return $next( $request );
        }
        $user = auth()->user();
        if ( $user ) {
            $redirectUrl = apply_filters( 'valpress/admin_access/forbidden/redirect_route', config( 'app.url' ) );
            $canAccessAdmin = apply_filters( 'valpress/admin_access/allowed', true );
            $errorMessage = apply_filters( 'valpress/admin_access/forbidden/error_message', __( 'a.The resource you are trying to access does not exist.' ) );
            if ( !$canAccessAdmin ) {
                return redirect()
                    ->to( $redirectUrl )
                    ->withError( $errorMessage );
            }
        }
        return $next( $request );
    }
}
