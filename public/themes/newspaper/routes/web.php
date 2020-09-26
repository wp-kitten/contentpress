<?php

use App\Models\Options;
use App\Models\PostType;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
 * Add custom routes or override existent ones
 *
 * Already loaded in the appropriate context
 * @see ./resources/routes/web.php
 */

//#! Override the default routes
//Route::get( "maintenance", "NewspaperThemeController@maintenance" )->name( "app.maintenance" );
Route::get( "/", "NewspaperThemeController@index" )->name( "app.home" );
//#!--

//#! Ajax
Route::get( "ajax", [ "uses" => "NewspaperAjaxController@index" ] )->name( "app.ajax" );
Route::post( "ajax", [ "uses" => "NewspaperAjaxController@index" ] )->name( "app.ajax" );

//#! Frontend routes
Route::get( "categories", "NewspaperThemeController@categories" )->name( "blog.categories" );

//#! Required in CategoriesWalker.. if not defined admin/post-type/category fails to load
Route::get( "categories/{slug}", "NewspaperThemeController@category" )->name( "blog.category" );

Route::get( "tags", "NewspaperThemeController@tags" )->name( "blog.tags" );
Route::get( "tags/{slug}", "NewspaperThemeController@tag" )->name( "blog.tag" );

Route::get( "authors", "NewspaperThemeController@authors" )->name( "blog.authors" );
Route::get( "author/{id}", "NewspaperThemeController@author" )->name( "blog.author" );

Route::any( "search", "NewspaperThemeController@search" )->name( "blog.search" );

//#! Special entries
Route::get( "lang/{code}", "NewspaperThemeController@lang" )->name( "app.switch_language" );
Route::post( 'comment/{post_id}', "NewspaperThemeController@__submitComment" )->name( 'app.submit_comment' );

/*
 * Register the route for user's custom home page, if:
 *  - the feed reader plugin is active
 *  - the theme option is enabled
 *
 * Requires auth & active user
 */
if ( defined( 'NPFR_PLUGIN_DIR_NAME' ) && np_userCustomHomeEnabled() ) {
    Route::get( "my-feeds", "NewspaperThemeController@userCustomHome" )
        ->middleware( [ 'auth', 'active_user', 'under_maintenance' ] )
        ->name( "app.my_feeds" );
    Route::get( "my-feeds/{category_slug}", "NewspaperThemeController@userCustomHomeCategoryView" )
        ->middleware( [ 'auth', 'active_user', 'under_maintenance' ] )
        ->name( "app.my_feeds.category" );
    Route::get( "my-feeds/{category_slug}/{feed_hash}", "NewspaperThemeController@userCustomHomeFeedView" )
        ->middleware( [ 'auth', 'active_user', 'under_maintenance' ] )
        ->name( "app.my_feeds.feed" );
}

/*
 * Dynamic routes for post types
 */
if ( Schema::hasTable( 'post_types' ) ) {
    $optionsClass = new Options();
    $postTypes = PostType::all();
    foreach ( $postTypes as $postType ) {
        //#! If the post type supports tags
        $allowTags = $optionsClass->getOption( "post_type_{$postType->name}_allow_tags", true );
        if ( $allowTags ) {
            //!# [post-type]-tags
            $route = $postType->name . '-tags';
            $method = str_replace( '-', '_', $route );
            Route::get( $route, "NewspaperThemeController@{$method}" )->name( "{$postType->name}.tags" );
        }
    }
}

/*
 * Admin routes
 */
Route::get( 'admin/themes/newspaper-options', 'NewspaperAdminController@themeOptionsPageView' )
    ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
    ->name( 'admin.themes.newspaper-options' );
Route::post( 'admin/themes/newspaper-options/save', 'NewspaperAdminController@themeOptionsSave' )
    ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
    ->name( 'admin.themes.newspaper-options.save' );
/*
 * Routes for user's feeds management
 */
if ( defined( 'NPFR_PLUGIN_DIR_NAME' ) ) {
    Route::get( 'admin/users/feeds', 'NewspaperAdminController@userFeedsView' )
        ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
        ->name( 'admin.users.feeds.all' );
    Route::post( 'admin/users/feeds/submit', 'NewspaperAdminController@userFeedSubmit' )
        ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
        ->name( 'admin.users.feeds.submit' );
    Route::post( 'admin/users/feeds/delete/{id}', 'NewspaperAdminController@userFeedDelete' )
        ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
        ->name( 'admin.users.feeds.delete' );
    Route::get( 'admin/users/feeds/edit/{id}', 'NewspaperAdminController@userFeedsEdit' )
        ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
        ->name( 'admin.users.feeds.edit' );
    Route::post( 'admin/users/feeds/update/{id}', 'NewspaperAdminController@userFeedsUpdate' )
        ->middleware( [ 'web', 'auth', 'active_user', 'under_maintenance' ] )
        ->name( 'admin.users.feeds.update' );
}
