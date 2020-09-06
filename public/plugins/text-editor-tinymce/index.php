<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */

use App\Helpers\PluginsManager;

define( 'CPTE_PLUGIN', true );
define( 'CPTE_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

if ( cp_is_admin() ) {
    require_once( dirname( __FILE__ ) . '/admin-hooks.php' );
}
else {
    require_once( dirname( __FILE__ ) . '/hooks.php' );
}

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'cpte', path_combine(
        PluginsManager::getInstance()->getPluginDirPath( CPTE_PLUGIN_DIR ),
        'lang'
    ) );
} );
