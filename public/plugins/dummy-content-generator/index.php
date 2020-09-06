<?php

//#! This plugin doesn't need to run in frontend
if ( !cp_is_admin() ) {
    return;
}

/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'DCG_PLUGIN', true );

define( 'DCG_PLUGIN_DIR', basename( ( dirname( __FILE__ ) ) ) );

require_once( dirname( __FILE__ ) . '/functions.php' );
require_once( dirname( __FILE__ ) . '/hooks.php' );

/*
 * Load plugin's admin routes
 */
require_once( path_combine( dirname( __FILE__ ), 'routes', 'admin.php' ) );
