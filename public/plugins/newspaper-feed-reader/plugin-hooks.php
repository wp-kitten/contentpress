<?php

use App\Helpers\ScriptsManager;
use App\Helpers\UserNotices;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

if ( !defined( 'NPFR_PLUGIN_DIR_NAME' ) ) {
    exit;
}

/**
 * Create the feeds table if it doesn't exist
 */
add_action( 'contentpress/plugin/activated', function ( $pluginDirName, $pluginInfo ) {
    //#! Run the migration
    if ( $pluginDirName == NPFR_PLUGIN_DIR_NAME ) {
        if ( !Schema::hasTable( 'feeds' ) ) {
            if ( !class_exists( 'CreateFeedsTable' ) ) {
                require_once( NPFR_PLUGIN_DIR_PATH . '/migrations/2020_09_07_144313_create_feeds_table.php' );
            }
            try {
                Artisan::call( 'migrate', [
                    '--path' => 'public/plugins/' . $pluginDirName . '/migrations/',
                ] );
            }
            catch ( Exception $e ) {
                UserNotices::getInstance()->addNotice( 'danger', '[' . $pluginDirName . '] Error: ' . $e->getMessage() );
            }
        }
    }
}, 10, 2 );

//#! Register the views path
add_filter( 'contentpress/register_view_paths', function ( $paths = [] ) {
    $viewPath = path_combine( NPFR_PLUGIN_DIR_PATH, 'views' );
    if ( !in_array( $viewPath, $paths ) ) {
        array_push( $paths, $viewPath );
    }
    return $paths;
}, 20 );

//
add_action( 'contentpress/admin/sidebar/menu', function () {
    if ( cp_current_user_can( 'manage_options' ) ) {
        ?>
        <li class="treeview <?php App\Helpers\MenuHelper::activateMenuItem( 'admin.feeds' ); ?>">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fas fa-rss"></i>
                <span class="app-menu__label"><?php esc_html_e( __( 'npfr::m.Feeds' ) ); ?></span>
                <i class="treeview-indicator fas fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item <?php App\Helpers\MenuHelper::activateSubmenuItem( 'admin.feed_reader.feeds.all' ); ?>"
                       href="<?php esc_attr_e( route( 'admin.feed_reader.feeds.all' ) ); ?>">
                        <?php esc_html_e( __( 'npfr::m.Manage' ) ); ?>
                    </a>
                </li>
                <li>
                    <a class="treeview-item <?php App\Helpers\MenuHelper::activateSubmenuItem( 'admin.feed_reader.feeds.trash' ); ?>"
                       href="<?php esc_attr_e( route( 'admin.feed_reader.feeds.trash' ) ); ?>">
                        <?php esc_html_e( __( 'npfr::m.Trash' ) ); ?>
                    </a>
                </li>

                <?php do_action( 'contentpress/admin/sidebar/menu/feeds' ); ?>
            </ul>
        </li>
        <?php
    }
} );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'npfr', path_combine( NPFR_PLUGIN_DIR_PATH, 'lang' ) );
} );

add_action( 'contentpress/admin/head', function () {
    //#! Make sure we're only loading in our page
    if ( request()->is( 'admin/feed-reader*' ) ) {
        ScriptsManager::enqueueStylesheet( 'npfr-plugin-styles', cp_plugin_url( NPFR_PLUGIN_DIR_NAME, 'res/styles.css' ) );
        ScriptsManager::enqueueFooterScript( 'npfr-plugin-scripts', cp_plugin_url( NPFR_PLUGIN_DIR_NAME, 'res/scripts.js' ) );
    }
}, 80 );

/**
 * Clear cache after feed(s) import
 */
add_action( 'newspaper-feed-reader/import-complete', function () {
    $cacheClass = app( 'cp.cache' );
    if ( $cacheClass ) {
        $cacheClass->clear();
    }
} );

