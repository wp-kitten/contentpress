<?php

namespace App\Http\Controllers;

class UnderMaintenanceController extends Controller
{

    public function maintenance()
    {
        if ( !cp_is_under_maintenance() ) {
            return redirect()->route( 'app.home' );
        }
        //#! Allows the maintenance view template to be filterable by plugins & themes
        return view( apply_filters( 'contentpress/maintenance/view', 'maintenance' ) );
    }
}
