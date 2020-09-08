<?php

use App\Helpers\UserNotices;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

require_once( dirname( __FILE__ ) . '/index.php' );

add_action( 'contentpress/plugin/deleted', function ( $pluginDirName ) {
    if ( NPFR_PLUGIN_DIR_NAME == $pluginDirName ) {
        //#! Drop table
        if ( Schema::hasTable( 'feeds' ) ) {
            if ( !class_exists( 'CreateFeedsTable' ) ) {
                require_once( NPFR_PLUGIN_DIR_PATH . '/migrations/2020_09_07_144313_create_feeds_table.php' );
            }
            try {
                Artisan::call( 'migrate:rollback', [
                    '--path' => 'public/plugins/' . $pluginDirName . '/migrations/',
                ] );
            }
            catch ( Exception $e ) {
                UserNotices::getInstance()->addNotice( 'danger', '[' . $pluginDirName . '] Error: ' . $e->getMessage() );
            }
        }
    }
}, 10 );
