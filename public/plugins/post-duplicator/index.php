<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'PD_PLUGIN', true );

//#! This plugin doesn't need to run in frontend
if ( !cp_is_admin() ) {
    return;
}

require_once( dirname( __FILE__ ) . '/hooks.php' );
