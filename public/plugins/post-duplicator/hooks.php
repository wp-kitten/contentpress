<?php use App\Helpers\PluginsManager;
use App\Helpers\ScriptsManager;

if ( !defined( 'PD_PLUGIN' ) ) {
    exit;
}

define( 'PD_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

add_action( 'contentpress/plugin/activated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' activated!' );
}, 10, 2 );

add_action( 'contentpress/plugin/deactivated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' deactivated!' );
}, 10, 2 );

/*
 * Load plugin's admin routes
 */
if ( cp_is_admin() ) {
    require_once( path_combine( dirname( __FILE__ ), 'routes', 'admin.php' ) );
}

add_action( 'contentpress/post/actions', function ( $postID ) {
    ?>
    <a href="#!" class="post-duplicate" onclick="event.preventDefault(); document.getElementById('form-post-duplicate-<?php esc_attr_e( $postID ); ?>').submit();"><?php esc_html_e( __( 'cppd::m.Clone' ) ); ?></a>
    <form id="form-post-duplicate-<?php esc_attr_e( $postID ); ?>" class="hidden" method="post" action="<?php esc_attr_e( route( 'admin.post_duplicator.duplicate', $postID ) ); ?>">
        <?php echo csrf_field(); ?>
    </form>
    <?php
} );

add_action( 'contentpress/admin/head', function () {
    ScriptsManager::enqueueStylesheet( 'post-duplicator-styles', cp_plugin_url( PD_PLUGIN_DIR, 'assets/styles.css' ) );
} );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'cppd', path_combine(
        PluginsManager::getInstance()->getPluginDirPath( PD_PLUGIN_DIR ),
        'lang'
    ) );
} );
