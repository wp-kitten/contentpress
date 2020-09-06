<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminControllerBase extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if ( !$this->current_user() || !cp_current_user_can( 'read' ) ) {
            return redirect()->route( 'app.home' );
        }
        return $this;
    }

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
        return view( 'admin.forbidden' )->with( 'message', $message );
    }
}
