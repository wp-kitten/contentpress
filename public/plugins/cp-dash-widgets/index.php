<?php
/**
 * Security flag to prevent plugin files from being loaded directly
 */
define( 'CPDW_PLUGIN', true );

/**
 * Stores the name of the plugin's directory
 */
define( 'CPDW_PLUGIN_DIR', basename( dirname( __FILE__ ) ) );

if ( cp_is_admin() ) {
    /*
     * Load widgets
     */
    require_once( dirname( __FILE__ ) . '/widgets/WidgetStatsPendingComments.php' );
    require_once( dirname( __FILE__ ) . '/widgets/WidgetStatsSpamComments.php' );

    add_filter( 'contentpress/dashboard/widgets/register', 'cpdw_register_widgets', 10, 1 );

    /**
     * Register widgets
     * @param array $widgets
     * @return array
     */
    function cpdw_register_widgets( $widgets = [] )
    {
        if ( !isset( $widgets[ 'App\\Widgets\\WidgetStatsPendingComments' ] ) ) {
            $widgets[ 'App\\Widgets\\WidgetStatsPendingComments' ] = 'cpdw_widget_pending_comments';
        }
        if ( !isset( $widgets[ 'App\\Widgets\\WidgetStatsSpamComments' ] ) ) {
            $widgets[ 'App\\Widgets\\WidgetStatsSpamComments' ] = 'cpdw_widget_spam_comments';
        }

        return $widgets;
    }

    /**
     * Register the path to the translation file that will be used depending on the current locale
     */
    add_action( 'contentpress/app/loaded', function () {
        cp_register_language_file( 'cpdw', path_combine( public_path( 'plugins' ), CPDW_PLUGIN_DIR, 'lang' ) );
    } );
}
