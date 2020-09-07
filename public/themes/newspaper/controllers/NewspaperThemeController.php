<?php

namespace App\Http\Controllers;


use Illuminate\View\View;

class NewspaperThemeController extends SiteController
{
    /**
     * Render the website's homepage.
     *
     * @return View
     */
    public function index()
    {
        return view( 'index' );
    }

}
