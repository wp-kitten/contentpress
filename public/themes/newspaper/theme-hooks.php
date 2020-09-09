<?php

use App\Helpers\PluginsManager;
use App\Helpers\ScriptsManager;
use App\Helpers\Theme;
use App\Helpers\UserNotices;
use App\Helpers\Util;
use App\Post;
use App\PostMeta;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Include theme's views into the global scope
 */
add_filter( 'contentpress/register_view_paths', function ( $paths = [] ) {
    $paths[] = path_combine( NP_THEME_DIR_PATH, 'views' );
    return $paths;
}, 80 );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'np', path_combine(
        NP_THEME_DIR_PATH,
        'lang'
    ) );
} );

/*
 * Load|output resources in the head tag
 */
add_action( 'contentpress/site/head', function () {

    $theme = new Theme( NP_THEME_DIR_NAME );

    //#! [DEBUG] Prevent the browser from caching resources
    $qv = ( env( 'APP_DEBUG' ) ? '?t=' . time() : '' );

    ScriptsManager::enqueueStylesheet( 'gfont-nunito', '//fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,400;1,600;1,700;1,800;1,900&display=swap' );
    ScriptsManager::enqueueStylesheet( 'gfont-libre-baskerville', '//fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap' );

    ScriptsManager::enqueueStylesheet( 'conveyor-ticker.css', $theme->url( 'assets/vendor/conveyor-ticker/jquery.jConveyorTicker.min.css' ) );
    ScriptsManager::enqueueStylesheet( 'bootstrap.css', $theme->url( 'assets/vendor/bootstrap/bootstrap.min.css' ) );
    ScriptsManager::enqueueStylesheet( 'theme-styles.css', $theme->url( 'assets/dist/css/theme-styles.css' ) . $qv );
    ScriptsManager::enqueueStylesheet( 'theme-overrides.css', $theme->url( 'assets/css/theme-overrides.css' ) . $qv );

    ScriptsManager::enqueueHeadScript( 'jquery.js', $theme->url( 'assets/vendor/jquery.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'popper.js', $theme->url( 'assets/vendor/popper/popper.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'bootstrap.js', $theme->url( 'assets/vendor/bootstrap/bootstrap.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'fa-kit.js', '//kit.fontawesome.com/cec4674fec.js' );
} );

/*
 * Load|output resources in the site footer
 */
add_action( 'contentpress/site/footer', function () {
    $theme = new Theme( NP_THEME_DIR_NAME );

    //#! [DEBUG] Prevent the browser from caching resources
    $qv = ( env( 'APP_DEBUG' ) ? '?t=' . time() : '' );

    ScriptsManager::enqueueFooterScript( 'siema.js', $theme->url( 'assets/vendor/siema.min.js' ) );
    ScriptsManager::enqueueFooterScript( 'masonry.js', $theme->url( 'assets/vendor/masonry.pkgd.min.js' ) );
    ScriptsManager::enqueueFooterScript( 'conveyor-ticker.js', $theme->url( 'assets/vendor/conveyor-ticker/jquery.jConveyorTicker.min.js' ) );
    ScriptsManager::enqueueFooterScript( 'theme-scripts.js', $theme->url( 'assets/dist/js/theme-scripts.js' ) . $qv );
    ScriptsManager::enqueueFooterScript( 'theme-custom-scripts.js', $theme->url( 'assets/js/theme-custom-scripts.js' ) . $qv );
} );

/*
 * Do something when plugins have loaded
 */
add_action( 'contentpress/plugins/loaded', function () {
    //...
} );

/**
 * Output some content right after the <body> tag
 */
add_action( 'contentpress/after_body_open', function () {
    //...
} );

/**
 * Filter classes applied to the <body> tag
 */
add_filter( 'contentpress/body-class', function ( $classes = [] ) {
    //...
    $classes[] = 'feed-reader';
    return $classes;
} );

//<editor-fold desc=":: MAIN MENU ::">
/**
 * Add custom menu items to the main menu
 */
add_action( 'contentpress/menu::main-menu/before', function () {
    echo '<div class="topnav bg-light text-dark">';
    $activeClass = ( Route::is( 'app.home' ) ? 'active' : '' );
    echo '<a href="' . route( 'app.home' ) . '" class="menu-item ' . $activeClass . '">' . esc_attr( __( 'np::m.Home' ) ) . '</a>';
} );
add_action( 'contentpress/menu::main-menu/after', function () {
    echo '<a href="#" class="icon btn-toggle-nav">&#9776;</a>';
    echo '</div>';
} );
add_action( 'contentpress/menu::main-menu', function () {
    //#! Render the link to the tags page
    $activeClass = ( Route::is( 'post.tags' ) ? 'active' : '' );
    echo '<a href="' . route( 'post.tags' ) . '" class="menu-item ' . esc_attr( $activeClass ) . '">' . __( 'np::m.Tags' ) . '</a>';

    //#! Render main categories (latest, limit 10)
    $categories = App\Category::where( 'language_id', cp_get_frontend_user_language_id() )->where( 'category_id', null )->latest()->limit( 10 )->get();
    if ( $categories ) {
        $activeClass = ( Str::containsAll( url()->current(), [ 'categories/' ] ) ? 'active' : '' );
        ?>
        <div class="has-submenu <?php esc_attr_e( $activeClass ); ?>">
            <button class="show-submenu">
                <?php esc_html_e( __( 'a.Categories' ) ); ?>
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="submenu-content">
                <?php
                foreach ( $categories as $category ) {
                    $url = cp_get_category_link( $category );
                    $activeClass = ( Str::containsAll( url()->current(), [ $url ] ) ? 'active' : '' );
                    echo '<a href="' . esc_attr( $url ) . '" class="menu-item ' . esc_attr( $activeClass ) . '">' . utf8_encode( $category->name ) . '</a>';
                }
                ?>
            </div>
        </div>
        <?php
    }
} );
//</editor-fold desc=":: MAIN MENU ::">

add_action( 'contentpress/submit_comment', 'np_theme_submit_comment', 10, 2 );

add_action( 'contentpress/post/footer', function ( Post $post ) {
    //#! Render the link back & the video if any
    if ( 'post' == $post->post_type()->first()->name ) {
        //#! Render the video if any
        $videoUrl = '';
        $postMeta = PostMeta::where( 'post_id', $post->id )
            ->where( 'language_id', $post->language_id )
            ->where( 'meta_name', '_video_url' )
            ->first();
        if ( $postMeta ) {
            $videoUrl = $postMeta->meta_value;
        }
        if ( $videoUrl ) {
            ?>
            <section class="entry-content section-video mb-3">
                <video src="<?php esc_attr_e( $videoUrl ); ?>" controls>
                    <embed src="<?php esc_attr_e( $videoUrl ); ?>"/>
                </video>
            </section>
            <?php
        }

        //#! Render tags, social icons, whatever...
        if ( $post->tags->count() ) {
            ?>
            <section class="entry-tags">
                <span class="tags"><?php esc_html_e( __( 'np::m.Tags:' ) ); ?></span>
                <?php
                foreach ( $post->tags as $tag ) {
                    wp_kses_e(
                        sprintf(
                            '<a href="%s" class="tag-link inline ml-15">%s</a>',
                            esc_attr( cp_get_tag_link( $tag ) ),
                            esc_html( $tag->name )
                        ),
                        [
                            'a' => [ 'class' => [], 'href' => [] ],
                        ]
                    );
                }
                ?>
            </section>
        <?php } ?>

        <?php

        //#! Back link to source
        $linkBack = '';
        $source = '';
        $postMeta = PostMeta::where( 'post_id', $post->id )
            ->where( 'language_id', $post->language_id )
            ->where( 'meta_name', '_link_back' )
            ->first();
        if ( $postMeta ) {
            $linkBack = $postMeta->meta_value;
            $source = Util::getDomain( $linkBack );
        }
        if ( $linkBack ) {
            ?>
            <section class="entry-credits mt-4 mb-4">
                <p>
                    <i class="fas fa-external-link-alt"></i>
                    <a href="<?php echo esc_attr( $linkBack ); ?>" target="_blank"><?php echo esc_html( __( 'np::m.View original article' ) ); ?></a>
                </p>
                <p>
                    <i class="fas fa-blog"></i>
                    <a href="//<?php echo esc_attr( $source ); ?>" target="_blank" title="<?php esc_attr_e( __( 'np::m.Source' ) ); ?>"><?php echo esc_html( $source ); ?></a>
                </p>
            </section>
            <?php
        }

        // {{-- Render the post navigation links --}}
        cp_posts_navigation( $post, '', true );

        $shareUrls = []; //NewspaperHelper::getShareUrls( $post );
        if ( !empty( $shareUrls ) ) {
            ?>
            <section class="entry-social-share">
                <ul>
                    <li>
                        <a class="facebook df-share" data-sharetip="<?php esc_attr_e( __( __( 'np::m.Share on Facebook!' ) ) ); ?>"
                           href="<?php esc_attr_e( $shareUrls[ 'facebook' ] ); ?>" rel="nofollow" target="_blank">
                            <i class="fa fa-facebook"></i>
                            Facebook</a>
                    </li>
                    <li>
                        <a class="twitter df-share" data-hashtags="" data-sharetip="<?php esc_attr_e( __( __( 'np::m.Share on Twitter!' ) ) ); ?>"
                           href="<?php esc_attr_e( $shareUrls[ 'twitter' ] ); ?>" rel="nofollow" target="_blank">
                            <i class="fa fa-twitter"></i>
                            Tweeter</a>
                    </li>
                    <li>
                        <a class="linkedin df-share" data-sharetip="<?php esc_attr_e( __( __( 'np::m.Share on Linkedin!' ) ) ); ?>"
                           href="<?php esc_attr_e( $shareUrls[ 'linkedin' ] ); ?>" rel="nofollow" target="_blank">
                            <i class="fa fa-linkedin"></i>
                            Linkedin</a>
                    </li>
                    <li>
                        <a class="pinterest df-pinterest" data-sharetip="<?php esc_attr_e( __( __( 'np::m.Pin it' ) ) ); ?>"
                           href="<?php esc_attr_e( $shareUrls[ 'pinterest' ] ); ?>" target="_blank">
                            <i class="fa fa-pinterest-p"></i>
                            Pinterest</a>
                    </li>
                    <li>
                        <a class="whatsapp df-share" data-sharetip="<?php esc_attr_e( __( __( 'np::m.Message it' ) ) ); ?>"
                           href="<?php esc_attr_e( $shareUrls[ 'whatsapp' ] ); ?>" target="_blank">
                            <i class="fa fa-whatsapp"></i>
                            WhatsApp</a>
                    </li>
                </ul>
            </section>
            <?php
        }
    }
} );

//#! Install and activate the theme's plugins
add_action( 'contentpress/plugins/loaded', 'np_activate_theme_plugins', 20 );
function np_activate_theme_plugins()
{
    $pluginsManager = PluginsManager::getInstance();

    if ( $pluginsManager->exists( 'newspaper-feed-reader' ) ) {
        return;
    }

    //#! Install and activate the plugin
    $pluginsDir = path_combine( NP_THEME_DIR_PATH, 'inc' );
    $files = glob( $pluginsDir . '/*.zip' );
    $errors = [];
    if ( !empty( $files ) ) {
        foreach ( $files as $filePath ) {
            $archiveName = basename( $filePath, '.zip' );
            $zip = new \ZipArchive();
            $pluginUploadDirPath = wp_normalize_path( public_path( 'uploads/tmp/' . $archiveName ) );
            if ( !File::isDirectory( $pluginUploadDirPath ) ) {
                File::makeDirectory( $pluginUploadDirPath, 0777, true );
            }

            if ( $zip->open( $filePath ) ) {
                $zip->extractTo( $pluginUploadDirPath );
                $zip->close();

                //#! Get the directory inside the uploads/tmp/$archiveName
                $pluginTmpDirPath = path_combine( $pluginUploadDirPath, $archiveName );

                //#! Move to the plugins directory
                $pluginDestDirPath = path_combine( $pluginsManager->getPluginsDir(), $archiveName );

                File::moveDirectory( $pluginTmpDirPath, $pluginDestDirPath );
                File::deleteDirectory( $pluginUploadDirPath );

                //#! Validate the uploaded plugin
                $pluginInfo = $pluginsManager->getPluginInfo( $archiveName );
                if ( false === $pluginInfo ) {
                    File::deleteDirectory( $pluginDestDirPath );
                    $errors[ $archiveName ] = [ __( 'a.The uploaded file is not valid.' ) ];
                    continue;
                }
                //#! Activate the plugin
                else {
                    $pluginsManager->activatePlugins( [ $archiveName ] );
                }
            }
            else {
                File::deleteDirectory( $pluginUploadDirPath );
            }
        }
        if ( !empty( $errors ) ) {
            $un = UserNotices::getInstance();
            foreach ( $errors as $k => $msgs ) {
                $un->addNotice( 'warning', $k . ': ' . implode( '<br/>', $msgs ) );
            }
        }
    }
}

if ( !cp_is_admin() ) {
    add_filter( 'contentpress/category/name', function ( string $name ) {
        return ucwords( utf8_encode( Str::lower( $name ) ) );
    } );
}
