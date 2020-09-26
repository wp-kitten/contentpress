<?php

/*
 * Add custom routes or override existent ones
 */

use Illuminate\Support\Facades\Route;

/*
 * Web routes
 */

//#! Already loaded in the appropriate context
//#! @see ./resources/routes/web.php

//#! Override the default route
Route::get( "/", "DefaultThemeController@index" )->name( "app.home" );

//#! Required in CategoriesWalker.. if not defined admin/post-type/category fails to load
Route::get( "categories/{slug}", "DefaultThemeController@category" )->name( "blog.category" );

Route::get( "tags", "DefaultThemeController@tags" )->name( "blog.tags" );
Route::get( "tags/{slug}", "DefaultThemeController@tag" )->name( "blog.tag" );

Route::get( "authors", "DefaultThemeController@authors" )->name( "blog.authors" );
Route::get( "author/{id}", "DefaultThemeController@author" )->name( "blog.author" );

Route::any( "search", "DefaultThemeController@search" )->name( "blog.search" );

//#! Special entries
Route::get( "lang/{code}", "DefaultThemeController@lang" )->name( "app.switch_language" );

Route::post( 'comment/{post_id}', "DefaultThemeController@__submitComment" )->name( 'app.submit_comment' );


