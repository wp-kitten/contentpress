<?php
/*
 * This file stores all filters registered by our application
 * ==============================================================================
 */

//#! Register the default widgets
add_filter( 'contentpress/dashboard/widgets', '__contentpress_dashboard_widgets', 10, 1 );
/**
 * Retrieve the list of all registered dashboard widgets
 * @param array $widgets
 * @return array
 */
function __contentpress_dashboard_widgets( $widgets = [] )
{
    if ( empty( $widgets ) ) {
        if ( !isset( $widgets[ 'section-1' ] ) ) {
            $widgets[ 'section-1' ] = [];
        }
        if ( !isset( $widgets[ 'section-2' ] ) ) {
            $widgets[ 'section-2' ] = [];
        }
        if ( !isset( $widgets[ 'section-3' ] ) ) {
            $widgets[ 'section-3' ] = [];
        }
        if ( !isset( $widgets[ 'section-4' ] ) ) {
            $widgets[ 'section-4' ] = [];
        }

        $widgets[ 'section-1' ][ 'App\\Widgets\\WidgetDraftPost' ] = 'widget_draft_post';

        $widgets[ 'section-2' ][ 'App\\Widgets\\WidgetStatsUsers' ] = 'widget_stats_users';

        $widgets[ 'section-3' ][ 'App\\Widgets\\WidgetStatsPosts' ] = 'widget_stats_posts';

        $widgets[ 'section-4' ][ 'App\\Widgets\\WidgetStatsComments' ] = 'widget_stats_comments';
        $widgets[ 'section-4' ][ 'App\\Widgets\\WidgetStatsPendingComments' ] = 'widget_stats_pending_comments';
        $widgets[ 'section-4' ][ 'App\\Widgets\\WidgetStatsSpamComments' ] = 'widget_stats_spam_comments';
    }

    return $widgets;
}
