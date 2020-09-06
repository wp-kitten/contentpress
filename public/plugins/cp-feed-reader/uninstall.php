<?php

use App\Options;

add_action( 'contentpress/plugin/deleted', function ( $pluginDirName ) {
    if ( 'cp-feed-reader' == $pluginDirName ) {
        $options = ( new Options() );
        $option = $options->where( 'name', CPFR_PROCESS_OPT_NAME )->first();
        if ( !$option ) {
            $option = $options->where( 'name', CPFR_PROCESS_OPT_NAME )->first();
        }
        if ( $option ) {
            $option->delete();
        }
    }
}, 10 );
