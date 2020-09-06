<?php

use App\Helpers\MenuHelper;
use App\Helpers\PluginsManager;
use Illuminate\Support\Facades\Request;

if ( !defined( 'DCG_PLUGIN' ) ) {
    exit;
}

add_action( 'contentpress/plugin/activated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' activated!' );
}, 10, 2 );

add_action( 'contentpress/plugin/deactivated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' deactivated!' );
}, 10, 2 );

//#! Register the views path
add_filter( 'contentpress/register_view_paths', 'cp_dcg_register_view_paths', 20 );
function cp_dcg_register_view_paths( $paths = [] )
{
    $viewPath = path_combine( public_path( 'plugins' ), basename( dirname( __FILE__ ) ), 'views' );
    if ( !in_array( $viewPath, $paths ) ) {
        array_push( $paths, $viewPath );
    }
    return $paths;
}

//
add_action( 'contentpress/admin/sidebar/menu', function () {
    if ( cp_current_user_can( 'publish_posts' ) ) {
        ?>
        <li class="treeview <?php MenuHelper::activateMenuItem( 'admin.dummy_content_generator' ); ?>">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-share"></i>
                <span class="app-menu__label"><?php esc_html_e( __( 'dcg::m.Dummy Content' ) ); ?></span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item <?php MenuHelper::activateSubmenuItem( 'admin.dummy_content_generator' ); ?>" href="<?php esc_attr_e( route( 'admin.dummy_content_generator' ) ); ?>">
                        <?php esc_html_e( __( 'dcg::m.Generator' ) ); ?>
                    </a>
                </li>
            </ul>
        </li>
        <?php
    }
} );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'dcg', path_combine(
        PluginsManager::getInstance()->getPluginDirPath( DCG_PLUGIN_DIR ),
        'lang'
    ) );
} );
