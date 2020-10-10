<?php

use App\Models\MediaFile;
use Illuminate\Support\Facades\Route;

/*
 * Add custom routes or override existent ones
 */

Route::get( 'admin/media/regenerate-thumbnails', function () {
    return view( 'cprt_regenerate_thumbnails' )->with( [
        'count' => MediaFile::count(),
    ] );
} )
    ->middleware( [ 'web', 'auth', 'active_user' ] )
    ->name( 'admin.media.regenerate_thumbnails' );

