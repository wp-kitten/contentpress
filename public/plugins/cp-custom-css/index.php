<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'CPCS_PLUGIN', true );

/**
 * Stores the name of the plugin's directory
 */
define( 'CPCS_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

function cpcsGetStylesheetUrl()
{
    $uploadsDir = cp_get_uploads_dir();
    return path_combine( $uploadsDir[ 'url' ], 'custom-styles.min.css' );
}

if ( cp_is_admin() ) {
    require_once( dirname( __FILE__ ) . '/admin/hooks.php' );
    require_once( dirname( __FILE__ ) . '/admin/routes.php' );
}
