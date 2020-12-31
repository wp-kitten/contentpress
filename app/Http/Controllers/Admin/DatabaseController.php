<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VPML;
use App\Helpers\Util;
use Illuminate\Http\Request;

class DatabaseController extends AdminControllerBase
{
    public function info()
    {
        return view( 'admin.database.info' )->with( [
            'table_rows_count' => VPML::getCountTableRows(),
        ] );
    }
}
