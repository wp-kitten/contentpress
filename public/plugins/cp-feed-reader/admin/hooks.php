<?php

use App\Helpers\ScriptsManager;

if ( !defined( 'CPFR_PLUGIN' ) ) {
    exit;
}

//#! Register the views path
add_filter( 'contentpress/register_view_paths', 'cpfr_register_view_paths', 20 );
function cpfr_register_view_paths( $paths = [] )
{
    $viewPath = path_combine( public_path( 'plugins' ), CPFR_PLUGIN_DIR, 'views' );
    if ( !in_array( $viewPath, $paths ) ) {
        array_push( $paths, $viewPath );
    }
    return $paths;
}

//
add_action( 'contentpress/admin/sidebar/menu', function () {
    if ( cp_current_user_can( 'manage_options' ) ) {
        ?>
        <li class="treeview <?php App\Helpers\MenuHelper::activateMenuItem( 'admin.feeds' ); ?>">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fas fa-rss"></i>
                <span class="app-menu__label"><?php esc_html_e( __( 'cpfr::m.Feeds' ) ); ?></span>
                <i class="treeview-indicator fas fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item <?php App\Helpers\MenuHelper::activateSubmenuItem( 'admin.feeds.all' ); ?>"
                       href="<?php esc_attr_e( route( 'admin.feeds.all' ) ); ?>">
                        <?php esc_html_e( __( 'cpfr::m.Manage' ) ); ?>
                    </a>
                </li>
                <li>
                    <a class="treeview-item <?php App\Helpers\MenuHelper::activateSubmenuItem( 'admin.feeds.trash' ); ?>"
                       href="<?php esc_attr_e( route( 'admin.feeds.trash' ) ); ?>">
                        <?php esc_html_e( __( 'cpfr::m.Trash' ) ); ?>
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
    cp_register_language_file( 'cpfr', path_combine( public_path( 'plugins' ), CPFR_PLUGIN_DIR, 'lang' ) );
} );

add_action( 'contentpress/admin/head', function () {
    //#! Make sure we're only loading in our page
    if ( request()->is( 'admin/feeds*' ) ) {
        ScriptsManager::enqueueStylesheet( 'cpfr-plugin-styles', cp_plugin_url( CPFR_PLUGIN_DIR, 'res/styles.css' ) );
        ScriptsManager::enqueueFooterScript( 'cpfr-plugin-scripts', cp_plugin_url( CPFR_PLUGIN_DIR, 'res/scripts.js' ) );
    }
}, 80 );
