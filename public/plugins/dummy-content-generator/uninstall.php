<?php

add_action( 'contentpress/plugin/deleted', function ( $pluginDirName ) {
    logger( 'Plugin '.$pluginDirName.' deleted!' );
}, 10 );
