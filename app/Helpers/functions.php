<?php
/*
 * Main functions file. This file is automatically loaded through composer.json
 */

use App\Helpers\CPML;
use App\Helpers\ImageHelper;
use App\Helpers\MenuWalkerFrontend;
use App\Helpers\ScriptsManager;
use App\Helpers\Theme;
use App\Helpers\ThemesManager;
use App\Helpers\TranslationsLoader;
use App\Helpers\Util;
use App\MediaFile;
use App\Menu;
use App\Options;
use App\Post;
use App\Settings;
use App\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

//#! Temp
$crtDirPath = dirname( __FILE__ );

require_once( $crtDirPath . '/inc/_globals.php' );

require_once( $crtDirPath . '/WP/wp-filters.php' );
require_once( $crtDirPath . '/WP/_wp-functions.php' );
require_once( $crtDirPath . '/WP/kses.php' );

require_once( $crtDirPath . '/inc/_actions.php' );
require_once( $crtDirPath . '/inc/_filters.php' );
require_once( $crtDirPath . '/inc/_fn_meta.php' );
require_once( $crtDirPath . '/inc/_fn_templates.php' );
require_once( $crtDirPath . '/inc/_fn_users.php' );
require_once( $crtDirPath . '/inc/_fn_comments.php' );
require_once( $crtDirPath . '/inc/_fn_notices.php' );
require_once( $crtDirPath . '/inc/_fn_multilanguage.php' );
require_once( $crtDirPath . '/inc/_cp_admin.php' );

//#! Remove
unset( $crtDirPath );

/**
 * Retrieve the application's version
 * @return string
 */
function cp_get_app_version()
{
    return CONTENTPRESS_VERSION;
}

/**
 * Retrieve the name of the current route
 * @return string
 */
function cp_get_current_route_name()
{
    $route = request()->route();
    if ( isset( $route->action[ 'as' ] ) && !empty( $route->action[ 'as' ] ) ) {
        return Str::lower( $route->action[ 'as' ] );
    }
    return '';
}

/**
 * helper method for the var_export function
 * @param null $data
 */
function vd( $data = null )
{
    echo '<div><pre>' . var_export( $data, 1 ) . '</pre></div>';
}

/**
 * @return bool false
 */
function __return_false()
{
    return false;
}

/**
 * @return bool true
 */
function __return_true()
{
    return true;
}

/**
 * @return null
 */
function __return_null()
{
    return null;
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 * @since 1.2.0
 *
 */
function trailingslashit( $string )
{
    return untrailingslashit( $string ) . '/';
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @param string $string What to remove the trailing slashes from.
 * @return string String without the trailing slashes.
 * @since 2.2.0
 *
 */
function untrailingslashit( $string )
{
    return rtrim( $string, '/\\' );
}

/**
 * Sanitizes a filename, replacing whitespace with dashes.
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trims period, dash and underscore from beginning
 * and end of filename. It is not guaranteed that this function will return a
 * filename that is allowed to be uploaded.
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 * @since 2.1.0
 *
 */
function sanitize_file_name( $filename )
{
    $filename_raw = $filename;
    $special_chars = [ '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', chr( 0 ) ];
    /**
     * Filters the list of characters to remove from a filename.
     *
     * @param array $special_chars Characters to remove.
     * @param string $filename_raw Filename as it was passed into sanitize_file_name().
     * @since 2.8.0
     *
     */
    $special_chars = apply_filters( 'sanitize_file_name_chars', $special_chars, $filename_raw );
    $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
    $filename = str_replace( $special_chars, '', $filename );
    $filename = str_replace( [ '%20', '+' ], '-', $filename );
    $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
    $filename = trim( $filename, '.-_' );

    if ( false === strpos( $filename, '.' ) ) {
        $mime_types = wp_get_mime_types();
        $filetype = wp_check_filetype( 'test.' . $filename, $mime_types );
        if ( $filetype[ 'ext' ] === $filename ) {
            $filename = 'unnamed-file.' . $filetype[ 'ext' ];
        }
    }

    // Split the filename into a base and extension[s]
    $parts = explode( '.', $filename );

    // Return if only one extension
    if ( count( $parts ) <= 2 ) {
        /**
         * Filters a sanitized filename string.
         *
         * @param string $filename Sanitized filename.
         * @param string $filename_raw The filename prior to sanitization.
         * @since 2.8.0
         *
         */
        return apply_filters( 'sanitize_file_name', $filename, $filename_raw );
    }

    // Process multiple extensions
    $filename = array_shift( $parts );
    $extension = array_pop( $parts );
    $mimes = get_allowed_mime_types();

    /*
     * Loop over any intermediate extensions. Postfix them with a trailing underscore
     * if they are a 2 - 5 character long alpha string not in the extension whitelist.
     */
    foreach ( (array)$parts as $part ) {
        $filename .= '.' . $part;

        if ( preg_match( '/^[a-zA-Z]{2,5}\d?$/', $part ) ) {
            $allowed = false;
            foreach ( $mimes as $ext_preg => $mime_match ) {
                $ext_preg = '!^(' . $ext_preg . ')$!i';
                if ( preg_match( $ext_preg, $part ) ) {
                    $allowed = true;
                    break;
                }
            }
            if ( !$allowed ) {
                $filename .= '_';
            }
        }
    }
    $filename .= '.' . $extension;
    /** This filter is documented in wp-includes/formatting.php */
    return apply_filters( 'sanitize_file_name', $filename, $filename_raw );
}

/**
 * Retrieve list of mime types and file extensions.
 *
 * @return array Array of mime types keyed by the file extension regex corresponding to those types.
 * @since 4.2.0 Support was added for GIMP (xcf) files.
 *
 * @since 3.5.0
 */
function wp_get_mime_types()
{
    /**
     * Filters the list of mime types and file extensions.
     *
     * This filter should be used to add, not remove, mime types. To remove
     * mime types, use the {@see 'upload_mimes'} filter.
     *
     * @param array $wp_get_mime_types Mime types keyed by the file extension regex
     *                                 corresponding to those types.
     * @since 3.5.0
     *
     */
    return apply_filters(
        'mime_types',
        [
            // Image formats.
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tiff|tif' => 'image/tiff',
            'ico' => 'image/x-icon',
            // Video formats.
            'asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp|3gpp' => 'video/3gpp', // Can also be audio
            '3g2|3gp2' => 'video/3gpp2', // Can also be audio
            // Text formats.
            'txt|asc|c|cc|h|srt' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'ics' => 'text/calendar',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',
            'vtt' => 'text/vtt',
            'dfxp' => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b' => 'audio/mpeg',
            'aac' => 'audio/aac',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'flac' => 'audio/flac',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska',
            // Misc application formats.
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            '7z' => 'application/x-7z-compressed',
            'exe' => 'application/x-msdownload',
            'psd' => 'application/octet-stream',
            'xcf' => 'application/octet-stream',
            // MS Office formats.
            'doc' => 'application/msword',
            'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps' => 'application/oxps',
            'xps' => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd' => 'application/wordperfect',
            // iWork formats.
            'key' => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages' => 'application/vnd.apple.pages',
        ]
    );
}

/**
 * Retrieve the file type from the file name.
 *
 * You can optionally define the mime array, if needed.
 *
 * @param string $filename File name or path.
 * @param array $mimes Optional. Key is the file extension with value as the mime type.
 * @return array Values with extension first and mime type.
 * @since 2.0.4
 *
 */
function wp_check_filetype( $filename, $mimes = null )
{
    if ( empty( $mimes ) ) {
        $mimes = get_allowed_mime_types();
    }
    $type = false;
    $ext = false;

    foreach ( $mimes as $ext_preg => $mime_match ) {
        $ext_preg = '!\.(' . $ext_preg . ')$!i';
        if ( preg_match( $ext_preg, $filename, $ext_matches ) ) {
            $type = $mime_match;
            $ext = $ext_matches[ 1 ];
            break;
        }
    }

    return compact( 'ext', 'type' );
}

/**
 * Retrieve list of allowed mime types and file extensions.
 *
 * @param User|null $user Optional. User to check. Defaults to current user.
 * @return array Array of mime types keyed by the file extension regex corresponding
 *               to those types.
 * @since 2.8.6
 *
 */
function get_allowed_mime_types( $user = null )
{
    $t = wp_get_mime_types();

    unset( $t[ 'swf' ], $t[ 'exe' ] );

    $unfiltered = cp_current_user_can( 'unfiltered_html' );

    if ( empty( $unfiltered ) ) {
        unset( $t[ 'htm|html' ], $t[ 'js' ] );
    }

    /**
     * Filters list of allowed mime types and file extensions.
     *
     * @param array $t Mime types keyed by the file extension regex corresponding to
     *                               those types. 'swf' and 'exe' removed from full list. 'htm|html' also
     *                               removed depending on '$user' capabilities.
     * @param User|null $user User ID, User object or null if not provided (indicates current user).
     * @since 2.0.0
     *
     */
    return apply_filters( 'upload_mimes', $t, $user );
}

/**
 * Combine the provided $args in a file system path. Without trailing slash.
 * @param mixed ...$args
 * @return string
 */
function path_combine( ...$args )
{
    if ( empty( $args ) ) {
        return '';
    }
    $entries = array_map( 'trailingslashit', $args );
    return untrailingslashit( wp_normalize_path( implode( '', $entries ) ) );
}

/**
 * Check to see whether or not the current request is for a page inside the admin area
 * @return bool
 */
function cp_is_admin()
{
    $request = Request::instance();
    $path = $request->path();
    if ( preg_match( '/^\badmin\b/', $path ) ) {
        return true;
    }
    return (bool)$request->is( 'admin.*' );
}

/**
 * Check to see whether or not the current request is an ajax request
 * @return bool
 */
function cp_is_ajax()
{
    $request = Request::instance();
    return (bool)$request->ajax();
}

/**
 * Enqueues the scripts required to render the Media Modal
 */
function cp_enqueue_media_scripts()
{
    add_action( 'contentpress/admin/footer', function () {
        echo view( 'admin.media.modal' )->with( [
            'files' => ( new MediaFile() )->where( 'language_id', CPML::getDefaultLanguageID() )->get(),
        ] )->toHtml();
    } );

    ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
    ScriptsManager::enqueueStylesheet( 'admin.media-styles', asset( '_admin/css/media/index.css' ) );

    ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
    ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );

    ScriptsManager::localizeScript( 'media-script-locale', 'MediaLocale', [
        'text_image_set' => __( 'a.Image uploaded' ),
        'text_image_removed' => __( 'a.Image removed' ),
        'text_media' => __( 'a.Media' ),
    ] );
    ScriptsManager::enqueueFooterScript( 'media-modal.js', asset( '_admin/js/media/modal.js' ) );
}

/**
 * Enqueue the scripts required to customize the text editor used for posts creation
 * @param int $currentPostID
 * @param string $screen The screen the editor is displayed onto
 * @param int $parentPostID The ID of the post being translated
 * @param int $languageID The ID of the language the post is being translated into
 */
function cp_enqueue_text_editor_scripts( $currentPostID = 0, $screen = '', $parentPostID = 0, $languageID = 0 )
{
    $post = Post::find( $currentPostID );

    ScriptsManager::localizeScript( 'posts-script-locale', 'PostsLocale', [
        'post_id' => $currentPostID,
        'text_image_set' => __( 'a.Image set' ),
        'text_image_removed' => __( 'a.Image removed' ),
        'text_description' => __( 'a.Short description here...' ),
        'language_id' => ( empty( $languageID ) ? $post->post_type->language_id : $languageID ),
        'post_type_id' => $post->post_type->id,

        //#! Screen: translate
        'parent_post_id' => $parentPostID,
        'current_post_id' => $currentPostID,
    ] );

    //#! Load the scripts to customize the text editor
    if ( 'post-new' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-create.js', asset( '_admin/js/posts/create.js' ) );
    }
    elseif ( 'post-edit' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-edit.js', asset( '_admin/js/posts/edit.js' ) );
    }
    elseif ( 'post-translate' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-translate.js', asset( '_admin/js/posts/translate.js' ) );
    }
}

/**
 * Check to see whether or not the specified page ID is the ID of the front page set in Settings > Reading
 * @param int $pageID
 * @return bool
 */
function cp_is_front_page( $pageID )
{
    $settings = new Settings();
    $showOnFront = $settings->getSetting( 'show_on_front', 'blog' );
    if ( 'page' == $showOnFront ) {
        $pageOnFront = $settings->getSetting( 'page_on_front' );
        return ( $pageOnFront == $pageID );
    }
    return false;
}

/**
 * Retrieve the list of links to the login, logout & register pages
 * @return array
 */
function cp_login_logout_links()
{
    return [
        'login' => route( 'login' ),
        'logout' => route( 'logout' ),
        'register' => ( Route::has( 'register' ) ? route( 'register' ) : '' ),
    ];
}

/*
 * Print header scripts/stylesheets/content
 */
function contentpressHead()
{
    do_action( 'contentpress/site/head' );
    ScriptsManager::printStylesheets();
    ScriptsManager::printLocalizedScripts();
    ScriptsManager::printHeadScripts();
}

/*
 * Print footer scripts/content
 */
function contentpressFooter()
{
    do_action( 'contentpress/site/footer' );
    ScriptsManager::printFooterScripts();
}

function cp_admin_head()
{
    do_action( 'contentpress/admin/head' );
    ScriptsManager::printStylesheets();
    ScriptsManager::printLocalizedScripts();
    ScriptsManager::printHeadScripts();
}

function cp_admin_footer()
{
    do_action( 'contentpress/admin/footer' );
    ScriptsManager::printFooterScripts();
}

/**
 * Register an image size
 * @param string $id
 * @param array $size
 */
function cp_add_image_size( $id, array $size = [ 'w' => null, 'h' => null ] )
{
    ImageHelper::addImageSize( $id, $size );
}

/**
 * Retrieve the application's charset
 * @return string
 */
function cp_get_charset()
{
    return env( 'APP_CHARSET', config( 'app.chaset', 'UTF-8' ) );
}

/**
 * Increments and returns a number that can safely be used as ID for repetitive content (ex: search forms, widgets, etc)
 * @return int
 * @uses $GLOBALS[ 'sid' ]
 */
function cp_get_global_id()
{
    $GLOBALS[ 'sid' ] += 1;
    return $GLOBALS[ 'sid' ];
}

/**
 * Register the path to the language file that will be loaded based on the current locale.
 * Themes and plugins should use this function in the "contentpress/app/loaded" action
 *
 * @param string $namespace The namespace to use for grouping the translations
 * @param string $langsDirPath The path to the languages directory, usually named "lang"
 * @param string $fileName The name of the translation file (without the .php file extension). It should always be just "m"
 */
function cp_register_language_file( $namespace, $langsDirPath, $fileName = 'm' )
{
    $loader = TranslationsLoader::getInstance();
    $loader->register( $namespace, $langsDirPath, $fileName );
}

/**
 * Retrieve the paths to the uploads directory
 * @return array
 */
function cp_get_uploads_dir()
{
    return [
        'dir' => untrailingslashit( wp_normalize_path( public_path( 'uploads' ) ) ),
        'url' => asset( 'uploads' ),
    ];
}

/**
 * Check to see whether or not the application is under maintenance
 * @return bool|mixed
 */
function cp_is_under_maintenance()
{
    if ( Schema::hasTable( 'settings' ) ) {
        $settings = new Settings();
        return $settings->getSetting( 'is_under_maintenance', false );
    }
    return false;
}

/**
 * Get the widgets saved in database
 * @return array
 */
function cp_get_registered_dashboard_widgets()
{
    $dashWidgets = apply_filters( 'contentpress/dashboard/widgets', ( new Options() )->getOption( '_dashboard_widgets', [] ) );
    $registeredWidgets = apply_filters( 'contentpress/dashboard/widgets/register', [] );

    $_tmpDashWidgets = [];
    foreach ( $dashWidgets as $section => $widgets ) {
        foreach ( $widgets as $className => $id ) {
            if ( class_exists( $className ) ) {
                array_push( $_tmpDashWidgets, $className );
            }
            else {
                unset( $dashWidgets[ $section ][ $className ] );
            }
        }
    }

    foreach ( $registeredWidgets as $className => $id ) {
        if ( !in_array( $className, $_tmpDashWidgets ) && class_exists( $className ) ) {
            $dashWidgets[ 'section-1' ][ $className ] = $id;
        }
    }
    return $dashWidgets;
}

function cp_get_post_view_url( Post $post )
{
    return Util::getPostViewUrl( $post );
}

/**
 * Retrieve the reference to the instance of the current theme
 * @return Theme|null
 */
function cp_get_current_theme(): ?Theme
{
    return ThemesManager::getInstance()->getActiveTheme();
}

/**
 * Retrieve the name of the curently active theme
 * @return string
 */
function cp_get_current_theme_name()
{
    if ( Schema::hasTable( 'options' ) ) {
        return ( new Options() )->getOption( ThemesManager::ACTIVE_THEME_NAME_OPT_NAME, 'default' );
    }
    return 'default';
}

/**
 * Retrieve the URL to the specified resource located inside the "plugins" directory
 * @param string $pluginName The directory name of the plugin
 * @param string $path Relative to the "plugin" directory
 * @return string
 */
function cp_plugin_url( $pluginName, $path )
{
    return asset( path_combine( 'plugins', $pluginName, $path ) );
}

/**
 * Retrieve the URL to the specified resource located inside the "plugins" directory
 * @param string $themeName The directory name of the theme
 * @param string $path Relative to the "theme" directory
 * @return string
 */
function cp_theme_url( $themeName, $path )
{
    return path_combine( ThemesManager::getInstance()->getThemesDirectoryUrl(), $themeName, $path );
}

/**
 * Check to see whether or not the specified menu exists
 * @param int|string $menuSlugOrID
 * @param int|null $languageID
 * @return mixed
 */
function cp_has_menu( $menuSlugOrID, $languageID = null )
{
    if ( empty( $languageID ) ) {
        $languageID = cp_get_frontend_user_language_id();
    }

    return Menu::where( 'id', intval( $menuSlugOrID ) )
        ->orWhere( 'slug', $menuSlugOrID )
        ->orWhere( 'name', $menuSlugOrID )
        ->where( function ( $query ) use ( $languageID ) {
            return $query->where( 'language_id', $languageID );
        } )
        ->first();
}

/**
 * Render a menu
 * @param string|int $menuSlugOrID The menu name, slug or ID
 */
function cp_menu( $menuSlugOrID )
{

    if ( cp_is_under_maintenance() && !cp_current_user_can( 'administrator' ) ) {
        return;
    }
    try {
        $walker = new MenuWalkerFrontend( $menuSlugOrID, cp_get_frontend_user_language_id() );
        $walker->outputHtml();
    }
    catch ( Exception $e ) {
        logger( $e->getMessage() );
    }
}

/**
 * Truncates a text with ellipsis
 * @param string $string
 * @param int $maxLength The min length the text should have to be truncated. Defaults to 50 characters
 * @param string $textMore
 * @return string
 */
function cp_ellipsis( string $string, int $maxLength = 50, string $textMore = '...' ): string
{
    if ( strlen( $string ) > $maxLength ) {
        return substr( $string, 0, $maxLength ) . $textMore;
    }
    return $string;
}

/**
 * Filter the specified category name
 * @param string $name
 *
 * @return string
 * @uses apply_filters('contentpress/category/name', $name)
 */
function cp_cat_name( string $name ): string
{
    return apply_filters( 'contentpress/category/name', $name );
}
