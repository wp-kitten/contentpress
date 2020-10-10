<?php

/**
 * Stores the name of the plugin's directory
 * @var string
 */
define( 'CPRT_PLUGIN_DIR_NAME', basename( dirname( __FILE__ ) ) );
/**
 * Stores the system path to the plugin's directory
 * @var string
 */
define( 'CPRT_PLUGIN_DIR_PATH', trailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) );

if ( cp_is_admin() ) {
    require_once( dirname( __FILE__ ) . '/admin/functions.php' );
    require_once( dirname( __FILE__ ) . '/admin/hooks.php' );
    require_once( dirname( __FILE__ ) . '/admin/routes.php' );
}
