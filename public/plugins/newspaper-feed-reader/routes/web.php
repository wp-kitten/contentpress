<?php

use App\Models\Feed;
use App\Helpers\FeedImporter;
use App\Helpers\UserNotices;
use App\Models\Options;
use App\Models\Settings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
 * Add custom routes or override existent ones
 */

Route::get( "admin/feed-reader/feeds", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    $feeds = [];
    $numFeeds = 0;
    if ( !Schema::hasTable( 'feeds' ) ) {
        UserNotices::getInstance()->addNotice( 'danger', __( 'npfr.The feeds table was not found. Have you forgotten to run the migration?' ) );
    }
    else {
        $feedsQuery = Feed::orderBy('created_at', 'desc');
        $numFeeds = $feedsQuery->count();
        $feeds = $feedsQuery->paginate( ( new Settings() )->getSetting( 'post_per_page' ) );
    }

    return view( 'npfr_index' )->with( [
        'feeds' => $feeds,
        'numFeeds' => $numFeeds,
        'categories' => npfrGetCategoriesTree(),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.all" );

Route::get( "admin/feed-reader/feeds/edit/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    return view( 'npfr_edit' )->with( [
        'feed' => Feed::findOrFail( $id ),
        'categories' => npfrGetCategoriesTree(),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.edit" );

Route::post( "admin/feed-reader/feeds/create", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    $request = request();

    $request->validate( [
        'url' => 'required',
        'id' => 'required|exists:categories',
    ] );

    $url = untrailingslashit( strtolower( $request->get( 'url' ) ) );
    if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The url is not valid.' ),
        ] );
    }

    $hash = md5( $url );
    $feed = Feed::where( 'hash', $hash )->withTrashed()->first();
    if ( $feed && $feed->id ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( "npfr::m.Another feed with the same url has already been registered. If it doesn't show up in the feeds list look for it in the trash." ),
        ] );
    }

    $result = Feed::create( [
        'hash' => md5( $url ),
        'url' => $url,
        'category_id' => intval( $request->get( 'id' ) ),
    ] );

    if ( $result ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.Feed successfully registered.' ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the feed could not be added.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.create" );

Route::post( "admin/feed-reader/feeds/update/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    $request = request();
    $request->validate( [
        'url' => 'required',
        'id' => 'required|exists:categories',
    ] );

    $url = untrailingslashit( strtolower( $request->get( 'url' ) ) );
    if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The url is not valid.' ),
        ] );
    }

    $feed = Feed::find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The specified feed was not found.' ),
        ] );
    }

    //#! Check to see whether or not the url changed
    if ( $feed->url != $url ) {
        if ( $feed->exists( $url ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'npfr::m.A feed with the same URL already exists.' ),
            ] );
        }
        $feed->url = $url;
        $feed->hash = md5( $url );
    }
    $feed->category_id = intval( $request->get( 'id' ) );
    $result = $feed->save();

    if ( $result ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.Feed updated.' ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the feed could not be updated.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.update" );

Route::post( "admin/feed-reader/feeds/delete/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    $feed = Feed::find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The specified feed was not found.' ),
        ] );
    }

    $deleted = $feed->delete();
    if ( $deleted ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.The feed has been moved to trash.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the feed could not be moved to trash.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.delete" );

Route::get( "admin/feed-reader/feeds/trash", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    return view( 'npfr_trash' )->with( [
        'feeds' => Feed::onlyTrashed()->paginate( ( new Settings() )->getSetting( 'post_per_page' ) ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.trash" );

Route::post( "admin/feed-reader/feeds/trash/restore/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feed = Feed::withTrashed()->find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The specified feed was not found.' ),
        ] );
    }

    $restored = $feed->restore();
    if ( $restored ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.The feed has been restored.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the feed could not be restored.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.trash.restore" );

Route::post( "admin/feed-reader/feeds/trash/delete/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feed = Feed::onlyTrashed()->find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The specified feed was not found.' ),
        ] );
    }

    $deleted = $feed->forceDelete();
    if ( $deleted ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.The feed has been deleted.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the feed could not be deleted.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.trash.delete" );

Route::post( "admin/feed-reader/feeds/trash/empty", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feeds = Feed::onlyTrashed()->get();
    if ( !$feeds ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.No feeds found in trash.' ),
        ] );
    }

    $hasErrors = false;
    foreach ( $feeds as $feed ) {
        if ( !$feed->forceDelete() ) {
            $hasErrors = true;
        }
    }

    if ( !$hasErrors ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'npfr::m.The trash has been emptied.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'npfr::m.An error occurred and the trash could not be emptied completely.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feed_reader.feeds.trash.empty" );

/*
 * @POST: Generate default categories
 */
Route::post( 'admin/feed-reader/import-default-content', function () {
    //#! Load seeder class
    $seederFilePath = path_combine( public_path( 'plugins' ), NPFR_PLUGIN_DIR_NAME, 'seeders', 'FeedSeeder.php' );
    require_once( $seederFilePath );

    try {
        Artisan::call( 'db:seed', [
            '--class' => 'FeedSeeder',
        ] );
    }
    catch ( Exception $e ) {
        return redirect()->route( 'admin.feed_reader.feeds.all' )->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.An error occurred while executing the seeder class.' ),
        ] );
    }

    return redirect()->route( 'admin.feed_reader.feeds.all' )->with( 'message', [
        'class' => 'success',
        'text' => __( 'npfr::m.Categories and feeds successfully created.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.import_default_content" );

/*
 * @POST: Import all feeds
 */
Route::post( 'admin/feed-reader/feeds/import', function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    //#! Check to see whether or not we're already importing
    if ( npfrImportingContent() ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.Cannot start a new import process. Timeout not expired yet.' ),
        ] );
    }

    $feeds = Feed::all();
    if ( empty( $feeds ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.No feeds found.' ),
        ] );
    }

    $feedUrls = Arr::pluck( $feeds, 'url' );

    $reader = new FeedImporter();
    $reader->register( $feedUrls );

    try {
        $reader->process();
    }
    catch ( \Exception $e ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.An error occurred: :error', [ 'error' => $e->getMessage() ] ),
        ] );
    }

    $options = ( new Options() );
    $expires = time() + CP_HOUR_IN_SECONDS;
    $options->addOption( NPFR_PROCESS_OPT_NAME, $expires );

    //#! Clear cache
    do_action( 'newspaper-feed-reader/import-complete' );

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __( 'npfr::m.The import process has completed.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.feed_reader.feeds.import' );

/*
 * @POST: Import a specific feed
 */
Route::post( 'admin/feed-reader/feeds/import/{id}', function ( $feedID ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'npfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    if ( empty( $feedID ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'npfr::m.The feed is missing.' ),
        ] );
    }

    $feed = Feed::find( $feedID );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'npfr::m.The feed was not found.' ),
        ] );
    }

    $feedUrl = $feed->url;
    if ( empty( $feedUrl ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.The feed url is missing.' ),
        ] );
    }

    $reader = new FeedImporter();
    $reader->register( [ $feedUrl ] );

    try {
        $reader->process();
    }
    catch ( \Exception $e ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'npfr::m.An error occurred: :error', [ 'error' => $e->getMessage() ] ),
        ] );
    }

    do_action( 'newspaper-feed-reader/import-complete' );

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __( 'npfr::m.The feed ":url" has been imported.', [ 'url' => $feedUrl ] ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.feed_reader.feeds.import_feed' );


/*
 * Frontend route
 *
 * Registers a route that whenever accessed it will trigger the feed import command
 */
Route::any('newspaper-feed-reader/import-feeds', function(){
    $r = Artisan::call('npfr_import_feeds');
    return [
        'success' => $r,
    ];
})->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] );
