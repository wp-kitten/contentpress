<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
 * Add custom routes or override existent ones
 */

/*
 * @GET: Show view
 */
Route::get( 'admin/dummy-content-generator', function () {

    if ( !cp_current_user_can( 'publish_posts' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'dcg::m.You are not allowed to access this page.' ),
        ] );
    }

    return view( 'dummy_content_generator' );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.dummy_content_generator' );

/*
 * @POST: Generate dummy content
 */
Route::post( 'admin/dummy-content-generator/generate', function () {
    //#! Load seeder class
    $seederFilePath = path_combine( public_path( 'plugins' ), basename( realpath( dirname( __FILE__ ) . '/../' ) ), 'seeders', 'DummyContentSeeder.php' );
    require_once( $seederFilePath );

    try {
        Artisan::call( 'db:seed', [
            '--class' => 'DummyContentSeeder',
        ] );
    }
    catch ( Exception $e ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'dcg::m.An error occurred while executing the seeder class.' ),
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __( 'dcg::m.Content generator ran successfully!' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.dummy_content_generator.generate' );

