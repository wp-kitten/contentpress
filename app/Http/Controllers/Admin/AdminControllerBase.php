<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminControllerBase extends Controller
{
    /**
     * This view can be used when a user tries to perform an action for which they don't have the capability to execute
     * @param string $message The message to be displayed to the user
     * @return View
     */
    public function _forbidden( $message = '' )
    {
        if ( empty( $message ) ) {
            $message = __( 'You are not allowed to perform this action.' );
        }
        return view( 'admin.forbidden' )->with( 'message', [
            'class' => 'danger',
            'text' => $message,
        ] );
    }
}
