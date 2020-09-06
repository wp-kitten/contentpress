<?php

use App\Feed;
use App\Options;
use App\Settings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
 * Add custom routes or override existent ones
 */

Route::post( 'admin/feeds/import', function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    //#! Check to see whether or not we're already importing
    if ( cpfrImportingContent() ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.Sorry, another import process is already in progress.' ),
        ] );
    }

    $options = ( new Options() );
    $expires = time() + ( 5 * 60 );
    $options->addOption( CPFR_PROCESS_OPT_NAME, $expires );

    try {
        Artisan::call( 'cp:feeder' );
    }
    catch ( Exception $e ) {
        //#! Delete option
        $option = $options->where( 'name', CPFR_PROCESS_OPT_NAME )->first();
        if ( $option ) {
            $option->delete();
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.An error occurred: :error.', [ 'error' => $e->getMessage() ] ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __( 'cpfr::m.The import process has completed.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.feeds.import' );

Route::get( "admin/feeds", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    return view( 'cpfr_index' )->with( [
        'feeds' => Feed::latest()->paginate( ( new Settings() )->getSetting( 'post_per_page' ) ),
        'categories' => cpfrGetCategoriesTree(),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.all" );

Route::get( "admin/feeds/edit/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    return view( 'cpfr_edit' )->with( [
        'feed' => Feed::findOrFail( $id ),
        'categories' => cpfrGetCategoriesTree(),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.edit" );

Route::post( "admin/feeds/create", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
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
            'text' => __( 'cpfr::m.The url is not valid.' ),
        ] );
    }

    $hash = md5( $url );
    $feed = Feed::where( 'hash', $hash )->withTrashed()->first();
    if ( $feed && $feed->id ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( "cpfr::m.Another feed with the same url has already been registered. If it doesn't show up in the feeds list look for it in the trash." ),
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
            'text' => __( 'cpfr::m.Feed successfully registered.' ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the feed could not be added.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.create" );

Route::post( "admin/feeds/update/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
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
            'text' => __( 'cpfr::m.The url is not valid.' ),
        ] );
    }

    $feed = Feed::find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.The specified feed was not found.' ),
        ] );
    }

    //#! Check to see whether or not the url changed
    if ( $feed->url != $url ) {
        if ( $feed->exists( $url ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'cpfr::m.A feed with the same URL already exists.' ),
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
            'text' => __( 'cpfr::m.Feed updated.' ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the feed could not be updated.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.update" );

Route::post( "admin/feeds/delete/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }

    $feed = Feed::find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.The specified feed was not found.' ),
        ] );
    }

    $deleted = $feed->delete();
    if ( $deleted ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'cpfr::m.The feed has been moved to trash.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the feed could not be moved to trash.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.delete" );

Route::get( "admin/feeds/trash", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    return view( 'cpfr_trash' )->with( [
        'feeds' => Feed::onlyTrashed()->paginate( ( new Settings() )->getSetting( 'post_per_page' ) ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.trash" );

Route::post( "admin/feeds/trash/restore/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feed = Feed::withTrashed()->find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.The specified feed was not found.' ),
        ] );
    }

    $restored = $feed->restore();
    if ( $restored ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'cpfr::m.The feed has been restored.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the feed could not be restored.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.trash.restore" );

Route::post( "admin/feeds/trash/delete/{id}", function ( $id ) {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feed = Feed::onlyTrashed()->find( $id );
    if ( !$feed ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.The specified feed was not found.' ),
        ] );
    }

    $deleted = $feed->forceDelete();
    if ( $deleted ) {
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'cpfr::m.The feed has been deleted.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the feed could not be deleted.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.trash.delete" );

Route::post( "admin/feeds/trash/empty", function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'cpfr::m.You are not allowed to perform this action.' ),
        ] );
    }
    $feeds = Feed::onlyTrashed()->get();
    if ( !$feeds ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.No feeds found in trash.' ),
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
            'text' => __( 'cpfr::m.The trash has been emptied.' ),
        ] );
    }
    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'cpfr::m.An error occurred and the trash could not be emptied completely.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.trash.empty" );

/*
 * @POST: Generate default categories
 */
Route::post( 'admin/feeds/import-default-content', function () {
    //#! Load seeder class
    $seederFilePath = path_combine( public_path( 'plugins' ), CPFR_PLUGIN_DIR, 'seeders', 'FeedSeeder.php' );
    require_once( $seederFilePath );

    try {
        Artisan::call( 'db:seed', [
            '--class' => 'FeedSeeder',
        ] );
    }
    catch ( Exception $e ) {
        return redirect()->route( 'admin.feeds.all' )->with( 'message', [
            'class' => 'danger',
            'text' => __( 'cpfr::m.An error occurred while executing the seeder class.'),
        ] );
    }

    return redirect()->route( 'admin.feeds.all' )->with( 'message', [
        'class' => 'success',
        'text' => __( 'cpfr::m.Categories and feeds successfully created.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( "admin.feeds.import_default_content" );
