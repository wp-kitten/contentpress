<?php use App\Helpers\ScriptsManager;

if ( !defined( 'CPTE_PLUGIN' ) ) {
    exit;
}

add_action( 'contentpress/site/head', function () {
    if ( cp_is_singular() || cp_is_page() ) {
        ScriptsManager::enqueueStylesheet( 'text-editor-quicksand-font-styles', 'https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap' );
        ScriptsManager::enqueueStylesheet( 'text-editor-styles', cp_plugin_url( basename( dirname( __FILE__ ) ), 'assets/css/frontend-styles.css' ) );
    }
}, 20 );

add_filter( 'contentpress/body-class', function ( $classes = [] ) {
    if ( cp_is_singular() || cp_is_page() ) {
        array_push( $classes, 'texteditor-plugin-single' );
    }
    return $classes;
} );

add_filter( 'contentpress/post-class', function ( $classes = [] ) {
    if ( cp_is_singular() || cp_is_page() ) {
        array_push( $classes, 'texteditor-plugin-single-post' );
    }
    return $classes;
} );

