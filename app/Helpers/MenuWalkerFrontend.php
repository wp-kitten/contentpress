<?php

namespace App\Helpers;

use App\Models\Category;
use App\Helpers\Menu\DropdownMenuBuilder;
use App\Helpers\Menu\MegaMenuBuilder;
use App\Helpers\Menu\MenuBuilderBasic;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Options;

class MenuWalkerFrontend implements IMenuWalker
{
    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var Menu|null
     */
    private $menu = null;

    /**
     * Holds the list of all menu items and their descendents
     * @var array
     */
    private $menuItems = [];

    /**
     * How to display the menu
     * @var string
     */
    private $displayAs = 'megamenu';

    /**
     * IMenuWalker constructor.
     * @param string|int $menu The menu name, slug or id
     * @param int|null $languageID
     * @throws \Exception if the menu doesn't exist
     */
    public function __construct( $menu, $languageID = null )
    {
        if ( empty( $languageID ) ) {
            $languageID = CPML::getDefaultLanguageID();
        }
        $menu = sanitize_file_name( $menu );
        $theMenu = Menu::where( 'language_id', $languageID )
            ->where( function ( $query ) use ( $menu ) {
                return $query->where( 'id', $menu )
                    ->orWhere( 'name', $menu )
                    ->orWhere( 'slug', $menu );
            } )
            ->first();

        if ( !$theMenu ) {
            throw new \Exception( __( 'a.The specified menu was not found.' ) );
        }

        $this->languageID = $languageID;
        $this->menu = $theMenu;
        $this->displayAs = ( new Options() )->getOption( "menu-{$theMenu->id}-display-as", 'megamenu' );
    }

    /**
     * Check to see whether or not the current menu has menu items
     * @return bool
     */
    public function hasMenuItems()
    {
        return ( count( $this->getMenuItems() ) > 0 );
    }

    /**
     * Retrieve the list of all menu items and their descendents (if any)
     * @return array
     */
    public function getMenuItems()
    {
        //#! First level menu items
        $menuItems = MenuItem::where( 'menu_id', $this->menu->id )->where( 'menu_item_id', null )->orderBy( 'menu_order', 'ASC' )->get();
        if ( $menuItems ) {
            foreach ( $menuItems as $menuItem ) {
                $this->menuItems[ $menuItem->id ] = [
                    'ref_item_id' => $menuItem->ref_item_id,
                    'menu_item_type_id' => $menuItem->menu_item_type_id,
                    'items' => [],
                ];
            }
            if ( !empty( $this->menuItems ) ) {
                $this->__processMenuItems();
            }
        }
        return $this->menuItems;
    }

    /**
     * Render the menu.
     * @param array $menuItems DO NOT USE, this parameter is ignored here
     */
    public function outputHtml( array $menuItems = [] )
    {
        if ( $this->menu && ( $this->hasMenuItems() || has_action( 'contentpress/menu::' . $this->menu->slug ) ) ) {
            do_action( "contentpress/menu::{$this->menu->slug}/before", $this->menu );
            if ( 'megamenu' == $this->displayAs ) {

                $menu = new MegaMenuBuilder( $this->menu, $this->menuItems );
                $menu->outputHtml();
            }
            elseif ( 'dropdown' == $this->displayAs ) {
                $menu = new DropdownMenuBuilder( $this->menu, $this->menuItems );
                $menu->outputHtml();
            }
            elseif ( 'basic' == $this->displayAs ) {
                $menu = new MenuBuilderBasic( $this->menu, $this->menuItems );
                $menu->outputHtml();
            }

            do_action( 'contentpress/menu::' . esc_attr( $this->menu->slug ), $this->menu );

            do_action( "contentpress/menu::{$this->menu->slug}/after", $this->menu );
        }
    }

    //<editor-fold desc=":: BUILD THE MENU TREE ::">
    //#! Process each first level menu item and recurse into it for children
    private function __processMenuItems()
    {
        foreach ( $this->menuItems as $menuItemID => $menuItemData ) {
            $this->menuItems[ $menuItemID ][ 'items' ] = $this->__processSubMenuItem( $menuItemID );
        }
    }

    private function __processSubMenuItem( $menuItemID )
    {
        $data = [];
        $children = $this->__getChildren( $menuItemID );
        if ( $children->count() > 0 ) {
            foreach ( $children as $child ) {
                if ( !isset( $data[ $child->id ] ) ) {
                    $data[ $child->id ] = [];
                }
                $data[ $child->id ][ 'ref_item_id' ] = $child->ref_item_id;
                $data[ $child->id ][ 'menu_item_type_id' ] = $child->menu_item_type_id;
                $data[ $child->id ][ 'items' ] = $this->__processSubMenuItem( $child->id );
            }
        }
        return $data;
    }

    private function __getChildren( $menuItemID )
    {
        return MenuItem::where( 'menu_item_id', $menuItemID )->orderBy( 'menu_order', 'ASC' )->get();
    }
    //</editor-fold desc=":: BUILD THE MENU TREE ::">

}
