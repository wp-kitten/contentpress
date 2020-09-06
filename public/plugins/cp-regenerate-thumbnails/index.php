<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'CPRT_PLUGIN', true );

/**
 * Stores the name of the plugin's directory
 */
define( 'CPRT_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

require_once( dirname( __FILE__ ) . '/admin/functions.php' );
require_once( dirname( __FILE__ ) . '/admin/hooks.php' );
require_once( dirname( __FILE__ ) . '/admin/routes.php' );
