<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'CPFR_PLUGIN', true );

/**
 * The name of the option storing whether the process is already in progress
 * @var string
 */
define( 'CPFR_PROCESS_OPT_NAME', 'cp_feed_reader_running' );

require_once( dirname( __FILE__ ) . '/functions.php' );

/**
 * Stores the name of the plugin's directory
 */
define( 'CPFR_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

if ( cp_is_admin() ) {
    require_once( dirname( __FILE__ ) . '/admin/hooks.php' );
    require_once( dirname( __FILE__ ) . '/admin/routes.php' );
}
