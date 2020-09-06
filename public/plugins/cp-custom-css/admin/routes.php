<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
 * Add custom routes or override existent ones
 */



Route::get( 'admin/cp-custom-css', function () {
    $cssData = '';

    $uploadsDir = cp_get_uploads_dir();
    $stylesheetFilePath = path_combine($uploadsDir['dir'], 'custom-styles.css');
    if ( File::isFile( $stylesheetFilePath ) ) {
        $cssData = trim( File::get( $stylesheetFilePath ) );
    }

    return view( 'cpcs_index' )->with( [
        'css_rules' => $cssData,
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.cp_custom_css' );

Route::post( 'admin/cp-custom-css/save', function () {
    if ( !cp_current_user_can( 'manage_options' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'cpcs::m.You are not allowed to perform this action.' ),
        ] );
    }
    $uploadsDir = cp_get_uploads_dir();
    $stylesheetFilePath = path_combine($uploadsDir['dir'], 'custom-styles.css');
    $stylesheetMinFilePath = path_combine($uploadsDir['dir'], 'custom-styles.min.css');

    $cssData = trim( wp_kses( request()->get( 'cpcs_custom_css' ), [] ) );
    File::put( $stylesheetFilePath, $cssData );
    $mincss = preg_replace('/\s+/msi', ' ', $cssData);
    File::put( $stylesheetMinFilePath, $mincss );

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __( 'cpcs::m.Custom styles have been saved.' ),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.cp_custom_css.save' );
