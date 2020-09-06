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
add_action( 'contentpress/app/loaded', function () {
    cp_register_language_file( 'df', path_combine(
        DEFAULT_THEME_DIR_PATH,
        'lang'
    ) );
} );

/*
 * Load|output resources in the head tag
 */
add_action( 'contentpress/site/head', function () {

    $theme = new Theme( DEFAULT_THEME_DIR_NAME );

    ScriptsManager::enqueueStylesheet( 'style.css', $theme->url( 'assets/css/styles.css' ) );

    ScriptsManager::enqueueHeadScript( 'jquery.js', '//code.jquery.com/jquery-3.5.1.min.js' );
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

/**
 * Filter classes applied to the <body> tag
 */
add_filter( 'contentpress/body-class', function ( $classes = [] ) {
    //...
    return $classes;
} );
