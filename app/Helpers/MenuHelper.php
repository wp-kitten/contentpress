<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * Check to see whether or not a matching menu item is active
     * @param string $partialRouteName
     * @return bool
     */
    public static function isActiveMenuItem( $partialRouteName )
    {
        $currentRoute = cp_get_current_route_name();

        if ( $currentRoute == $partialRouteName ) {
            return true;
        }
        return preg_match( '/^' . $partialRouteName . '/', $currentRoute );
    }

    /**
     * @param string $partialRouteName The string to search for a match in the current route name
     * @uses cp_get_current_route_name()
     */
    public static function activateMenuItem( $partialRouteName )
    {
        echo( MenuHelper::isActiveMenuItem( $partialRouteName ) ? 'is-expanded ' : '' );
    }

    /**
     * Utility method to open submenus if parent menu item is active
     * @param string $partialRouteName
     */
    public static function openSubmenus( $partialRouteName )
    {
        echo( MenuHelper::isActiveMenuItem( $partialRouteName ) ? 'show' : '' );
    }

    /**
     * @param string $routeName
     * @param bool $partial Whether or not to match only a partial section of the provided route
     * @uses cp_get_current_route_name()
     */
    public static function activateSubmenuItem( $routeName, $partial = false )
    {
        if ( $partial ) {
            echo( preg_match( '/^' . $routeName . '/', cp_get_current_route_name() ) ? 'active' : '' );
        }
        else {
            echo( cp_get_current_route_name() == $routeName ? 'active' : '' );
        }
    }

}
