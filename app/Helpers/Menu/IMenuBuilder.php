<?php

namespace App\Helpers\Menu;

use App\Models\Menu;

interface IMenuBuilder
{
    /**
     * MenuBuilderBase constructor.
     * @param Menu $menu
     * @param array $menuItems
     */
    function __construct( Menu $menu, array $menuItems = [] );

    /**
     * Display the menu
     * @return void
     */
    public function outputHtml();
}
