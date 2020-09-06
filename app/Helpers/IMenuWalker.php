<?php

namespace App\Helpers;

interface IMenuWalker
{
    /**
     *  constructor.
     * @param string|int $menu The menu name, slug or id
     * @param int $languageID
     * @throws \Exception if the menu doesn't exist
     */
    public function __construct( $menu, $languageID = 0 );

    /**
     * Check to see whether or not the current menu has menu items
     * @return bool
     */
    public function hasMenuItems();

    /**
     * Retrieve the list of all menu items and their descendents (if any)
     * @return array
     */
    public function getMenuItems();

    /**
     * Render the menu
     * @param array $menuItems The optional list of menu items to render
     */
    public function outputHtml(array $menuItems = []);
}
