<?php

use App\Helpers\ScriptsManager;
use App\Helpers\Theme;

/**
 * Include theme's views into the global scope
 */
add_filter( 'contentpress/register_view_paths', function ( $paths = [] ) {
    $paths[] = path_combine( DEFAULT_THEME_DIR_PATH, 'views' );
    return $paths;
}, 20 );

/**
 * Register the path to the translation file that will be used depending on the current locale
 */
cp_register_language_file( 'cpdt', path_combine(
    DEFAULT_THEME_DIR_PATH,
    'lang'
) );


/*
 * Load|output resources in the head tag
 */
add_action( 'contentpress/site/head', function () {

    $theme = new Theme( DEFAULT_THEME_DIR_NAME );

    ScriptsManager::enqueueStylesheet( 'gfont-montserrat', '//fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&display=swap' );
    ScriptsManager::enqueueStylesheet( 'gfont-open-sans', '//fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700;1,800&display=swap' );

    ScriptsManager::enqueueStylesheet( 'bootstrap.css', $theme->url( 'assets/vendor/bootstrap/css/bootstrap.min.css' ) );
    ScriptsManager::enqueueStylesheet( 'theme-style.css', $theme->url( 'assets/dist/css/theme-styles.css' ) );

    ScriptsManager::enqueueHeadScript( 'jquery.js', $theme->url( 'assets/vendor/jquery.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'popper.js', $theme->url( 'assets/vendor/popper.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'bootstrap.js', $theme->url( 'assets/vendor/bootstrap/js/bootstrap.min.js' ) );
    ScriptsManager::enqueueHeadScript( 'fa-kit.js', '//kit.fontawesome.com/cec4674fec.js' );
    ScriptsManager::enqueueFooterScript( 'theme-scripts.js', $theme->url( 'assets/js/theme-scripts.js' ) );
} );

/*
 * Load|output resources in the site footer
 */
add_action( 'contentpress/site/footer', function () {
    //...
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

add_action( 'contentpress/post/footer', function ( $post ) {
    $tags = $post->tags()->get();
    if ( $tags ) {
        ?>
        <div class="post-meta">
            <div class="post-tags">
                <strong><?php esc_html_e( __( 'cpdt::m.Tags:' ) ); ?></strong>
                <?php
                foreach ( $tags as $tag ) {
                    echo '<a href="' . esc_attr( cp_get_tag_link( $tag ) ) . '">' . $tag->name . '</a>';
                }
                ?>
            </div>
        </div>
        <?php
    }
} );

/**
 * Filter classes applied to the <body> tag
 */
add_filter( 'contentpress/body-class', function ( $classes = [] ) {
    $classes[] = 'theme-default';
    return $classes;
} );
