<?php

namespace App\Http\Controllers;

class UnderMaintenanceController extends Controller
{

    public function maintenance()
    {
        if ( !vp_is_under_maintenance() ) {
            return redirect()->route( 'app.home' );
        }
        return view( 'maintenance' );
    }
}
