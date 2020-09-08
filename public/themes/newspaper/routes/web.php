<?php

use Illuminate\Support\Facades\Route;

/*
 * Add custom routes or override existent ones
 *
 * Already loaded in the appropriate context
 * @see ./resources/routes/web.php
 */

//#! Override the default routes
Route::get( "maintenance", "NewspaperThemeController@maintenance" )->name( "app.maintenance" );
Route::get( "/", "NewspaperThemeController@index" )->name( "app.home" );
//#!--

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


