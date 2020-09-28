<?php

use App\Models\Feed;
use App\Helpers\FeedImporter;
use App\Models\Options;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you define all of your plugin's Closure based console
| commands.
|
*/
//#! Cron job: every hour
//#! 0	*	*	*	* /usr/local/bin/php -q /home/appvyxhr5zi6/public_html/artisan npfr_import_feeds >> /dev/null 2>&1
Artisan::command( 'npfr_import_feeds', function () {
    //#! Check to see whether or not we're already importing
    if ( npfrImportingContent() ) {
//        logger( 'Cannot start a new import process. Timeout not expired yet.' );
        return 0;
    }

    $options = ( new Options() );
    $expires = time() + CP_HOUR_IN_SECONDS;
    $options->addOption( NPFR_PROCESS_OPT_NAME, $expires );

    $feeds = Feed::all();
    if ( empty( $feeds ) ) {
        logger( 'No feeds found.' );
        return 0;
    }

    $feedUrls = Arr::pluck( $feeds, 'url' );

    $reader = new FeedImporter();
    $reader->register( $feedUrls );

    try {
        $reader->process();
    }
    catch ( \Exception $e ) {
        logger( 'An error occurred: ' . $e->getMessage() );
        return 0;
    }

    //#! Clear cache
    do_action( 'newspaper-feed-reader/import-complete' );
    return 1;
} )->describe( 'Import feeds' );
