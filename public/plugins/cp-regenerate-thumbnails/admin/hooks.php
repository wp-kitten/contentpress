<?php

use App\Helpers\MenuHelper;
use App\Helpers\ScriptsManager;
use App\Models\MediaFile;

if ( !defined( 'CPRT_PLUGIN_DIR_NAME' ) ) {
    exit;
}

//#! Register the views path
add_filter( 'contentpress/register_view_paths', 'cprt_register_view_paths', 20 );
function cprt_register_view_paths( $paths = [] )
{
    $viewPath = path_combine( public_path( 'plugins' ), CPRT_PLUGIN_DIR_NAME, 'views' );
    if ( !in_array( $viewPath, $paths ) ) {
        array_push( $paths, $viewPath );
    }
    return $paths;
}

//#! Add the sidebar menu entry
add_action( 'contentpress/admin/sidebar/menu/media', function () {
    if ( cp_current_user_can( 'list_media' ) ) {
        ?>
        <li>
            <a class="treeview-item <?php MenuHelper::activateSubmenuItem( 'admin.media.regenerate_thumbnails' ); ?>"
               href="<?php esc_attr_e( route( 'admin.media.regenerate_thumbnails' ) ); ?>">
                <?php esc_html_e( __( 'cprt::m.Regenerate Thumbnails' ) ); ?>
            </a>
        </li>
        <?php
    }
} );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'cprt', path_combine( public_path( 'plugins' ), CPRT_PLUGIN_DIR_NAME, 'lang' ) );
} );

$mediaFiles = MediaFile::all();
$mediaFilesArray = [];
if ( $mediaFiles ) {
    foreach ( $mediaFiles as $mediaFile ) {
        array_push( $mediaFilesArray, $mediaFile->id );
    }
}

add_action( 'contentpress/admin/head', function () use ( $mediaFilesArray ) {
    if ( request()->is( 'admin/media/regenerate-thumbnails' ) ) {
        ScriptsManager::localizeScript( 'cprt-plugin-locale', 'RegenerateThumbnailsLocale', [
            'images_count' => count( $mediaFilesArray ),
            'files' => $mediaFilesArray,
            'text_completed' => esc_js( __( 'cprt::m.Done!' ) ),
        ] );
        ScriptsManager::enqueueFooterScript( 'regenerate-thumbnails.js', cp_plugin_url( CPRT_PLUGIN_DIR_NAME, 'assets/regenerate-thumbnails.js' ) );
    }
}, 20 );
