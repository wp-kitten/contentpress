<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Helpers\ThemesManager;
use App\PostType;
use App\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
 * The default auth options. These can be overridden in Admin > Settings
 */
$authOptions = [
//#! Disable Registration
    'register' => false,

//#! Enable Email Verification
    'verify' => true,

//#! Allows use of password confirmation for routes (see admin routes)
    'confirm' => true,

//#! Enable Reset Password
    'reset' => true,
];

//#! Check settings and enable/disable registration
if ( Schema::hasTable( 'settings' ) ) {
    $settings = new Settings();
    $authOptions[ 'register' ] = $settings->getSetting( 'user_registration_open', false );
    $authOptions[ 'verify' ] = $settings->getSetting( 'registration_verify_email', false );
    $authOptions[ 'reset' ] = $settings->getSetting( 'allow_user_reset_password', true );
    //#! Allows use of password confirmation for routes (see admin routes) [Timeouts after 30 minutes]
    //#! Ex: Route::get( "users/edit/{id}", [ "uses" => "UsersController@showEditPage" ] )->name( "admin.users.edit" )->middleware('password.confirm');
    $authOptions[ 'confirm' ] = true;
}

//#! Under Maintenance route
Route::any( "maintenance", "UnderMaintenanceController@maintenance" )->middleware( [ 'web' ] )->name( "app.maintenance" );

/*
 * Frontend routes
 *
 * Since these can be defined by each theme, none of them is implemented here.
 * The route to home MUST be defined here though since it's used outside a theme's scope
 */
Route::group( [
    'prefix' => '/', 'middleware' => [ 'web', 'active_user', 'under_maintenance' ],
], function () {
    //#! Default app routes -- these can be overridden in themes & plugins
    Route::get( "/", "SiteController@index" )->name( "app.home" );
    Route::get( "/404", "SiteController@error404" );
    Route::get( "/500", "SiteController@error500" );
    //#!--

    //#! Load active theme's routes if provided
    $tm = ThemesManager::getInstance();
    $activeTheme = $tm->getActiveTheme();
    if ( $activeTheme ) {
        $frontendRoutesFilePath = path_combine( $tm->getThemesDirectoryPath(), $activeTheme->get( 'name' ), 'routes/web.php' );
        if ( File::isFile( $frontendRoutesFilePath ) ) {
            require_once( $frontendRoutesFilePath );
        }
    }
} );

/*
 * Auth Routes
 * vendor\laravel\ui\src\AuthRouteMethods.php
 */
Auth::routes( $authOptions );

/*
 * Admin Routes
 */
Route::group(
    [
        "prefix" => "admin", "namespace" => "Admin",
        'middleware' => [ 'web', 'auth', 'active_user', 'under_maintenance' ],

    ], function () {
    Route::get( "ajax", [ "uses" => "AjaxController@index" ] )->name( "admin.ajax" );
    Route::post( "ajax", [ "uses" => "AjaxController@index" ] )->name( "admin.ajax" );

    //#! Switch language admin
    Route::get( "lang/{code}", [ "uses" => "DashboardController@lang" ] )->name( "admin.dashboard.lang_switch" );

    Route::get( "/", [ "uses" => "DashboardController@index" ] )->name( "admin.dashboard" );
    Route::get( "dashboard_edit", [ "uses" => "DashboardController@showEditDashboardView" ] )->name( "admin.dashboard.edit" );
    Route::post( "dashboard_refresh_stats", [ "uses" => "DashboardController@__refreshStats" ] )->name( "admin.dashboard.refresh_stats" );
    Route::post( "reinstall-app", [ "uses" => "DashboardController@__reinstallApp" ] )->name( "admin.dashboard.reinstall_app" );

    Route::get( "updates", [ "uses" => "DashboardController@showUpdatesView" ] )->name( "admin.dashboard.updates" );
    Route::post( "updates/check", [ "uses" => "DashboardController@__checkForUpdates" ] )->name( "admin.dashboard.check_for_updates" );
    Route::post( "update/theme/{file_name}", [ "uses" => "DashboardController@__update_theme" ] )->name( "admin.dashboard.update.theme" );
    Route::post( "update/plugin/{file_name}", [ "uses" => "DashboardController@__update_plugin" ] )->name( "admin.dashboard.update.plugin" );
    Route::post( "update/core/{version}", [ "uses" => "DashboardController@__update_core" ] )->name( "admin.dashboard.update.core" );

    Route::get( "users", [ "uses" => "UsersController@index" ] )->name( "admin.users.all" );
    Route::get( "users/add", [ "uses" => "UsersController@showCreatePage" ] )->name( "admin.users.add" );
    Route::get( "users/edit/{id}", [ "uses" => "UsersController@showEditPage" ] )->name( "admin.users.edit" )->middleware( 'password.confirm' );
    Route::get( "users/block/{id}", [ "uses" => "UsersController@__block" ] )->name( "admin.users.block" );
    Route::get( "users/unblock/{id}", [ "uses" => "UsersController@__unblock" ] )->name( "admin.users.unblock" );
    Route::get( "users/delete/{id}", [ "uses" => "UsersController@__delete" ] )->name( "admin.users.delete" );
    Route::post( "users/insert", [ "uses" => "UsersController@__insert" ] )->name( "admin.users.insert" );
    Route::post( "users/update", [ "uses" => "UsersController@__update" ] )->name( "admin.users.update" );
    Route::post( "users/profile/update/{id}", [ "uses" => "UsersController@__updateProfile" ] )->name( "admin.users.update_profile" );

    Route::get( "settings", [ "uses" => "SettingsController@index" ] )->name( "admin.settings.all" );
    Route::get( "settings/languages", [ "uses" => "SettingsController@languages" ] )->name( "admin.settings.languages" );
    Route::get( "settings/reading", [ "uses" => "SettingsController@reading" ] )->name( "admin.settings.reading" );
    Route::get( "settings/post_types", [ "uses" => "SettingsController@post_types" ] )->name( "admin.settings.post_types" );
    Route::get( "settings/post_types/edit/{id}", [ "uses" => "SettingsController@showPostTypeEditPage" ] )->name( "admin.settings.post_types.edit" );
    Route::get( "settings/post_types/delete/{id}", [ "uses" => "SettingsController@__deletePostType" ] )->name( "admin.settings.post_types.delete" );
    Route::get( "settings/cache/clear", [ "uses" => "SettingsController@__clearCache" ] )->name( "admin.cache.clear" );

    Route::post( "settings/general/update", [ "uses" => "SettingsController@__updateSettings" ] )->name( "admin.settings.general.update" );
    Route::post( "settings/languages/update", [ "uses" => "SettingsController@__updateLanguages" ] )->name( "admin.settings.languages.update" );
    Route::post( "settings/reading/update", [ "uses" => "SettingsController@__updateReadingSettings" ] )->name( "admin.settings.reading.update" );
    Route::post( "settings/post_types/add", [ "uses" => "SettingsController@__insertPostType" ] )->name( "admin.settings.post_types.add" );
    Route::post( "settings/post_types/update/{id}", [ "uses" => "SettingsController@__updatePostTypeDefault" ] )->name( "admin.settings.post_types.update" );
    Route::post( "settings/post_types/translate/{post_id}/{language_id}/{new_post_id?}", [ "uses" => "SettingsController@__translate" ] )->name( "admin.settings.post_types.translate" );

    //#! Plugins
    Route::get( "plugins", [ "uses" => "PluginsController@index" ] )->name( "admin.plugins.all" );
    Route::get( "plugins/add", [ "uses" => "PluginsController@renderAddView" ] )->name( "admin.plugins.add" );
    Route::get( "plugins/delete/{plugin_dir_name}", [ "uses" => "PluginsController@__delete__GET" ] )->name( "admin.plugins.delete" );
    Route::get( "plugins/activate/{plugin_dir_name}", [ "uses" => "PluginsController@__activatePlugin__GET" ] )->name( "admin.plugins.activate__get" );
    Route::post( "plugins/activate", [ "uses" => "PluginsController@__activatePlugins" ] )->name( "admin.plugins.activate__post" );
    Route::get( "plugins/deactivate/{plugin_dir_name}", [ "uses" => "PluginsController@__deactivatePlugin__GET" ] )->name( "admin.plugins.deactivate__get" );
    Route::post( "plugins/deactivate", [ "uses" => "PluginsController@__deactivatePlugins__POST" ] )->name( "admin.plugins.deactivate__post" );

    //#! Themes
    Route::get( "themes", [ "uses" => "ThemesController@index" ] )->name( "admin.themes.all" );
    Route::get( "themes/add", [ "uses" => "ThemesController@renderAddView" ] )->name( "admin.themes.add" );
    Route::get( "themes/activate/{theme_name}", [ "uses" => "ThemesController@__activate" ] )->name( "admin.themes.activate" );
    Route::get( "themes/delete/{theme_name}", [ "uses" => "ThemesController@__delete" ] )->name( "admin.themes.delete" );

    //#! Media
    Route::get( "media", [ "uses" => "MediaController@index" ] )->name( "admin.media.all" );
    Route::get( "media/add", [ "uses" => "MediaController@showAddView" ] )->name( "admin.media.add" );
    Route::get( "media/edit/{id}", [ "uses" => "MediaController@showEditView" ] )->name( "admin.media.edit" );
    Route::any( "media/search/{s?}", [ "uses" => "MediaController@showSearchView" ] )->name( "admin.media.search" );
    Route::post( "media/update/{id}", [ "uses" => "MediaController@__update" ] )->name( "admin.media.update" );
    Route::post( "media/delete/{id}", [ "uses" => "MediaController@__delete" ] )->name( "admin.media.delete" );

    Route::get( "menus", [ "uses" => "MenuController@index" ] )->name( "admin.menus.all" );
    Route::get( "menus/add", [ "uses" => "MenuController@showCreatePage" ] )->name( "admin.menus.add" );
    Route::get( "menus/edit/{id}", [ "uses" => "MenuController@showEditPage" ] )->name( "admin.menus.edit" );
    Route::post( "menus/create", [ "uses" => "MenuController@__insert" ] )->name( "admin.menus.create" );
    Route::post( "menus/update/{id}", [ "uses" => "MenuController@__update" ] )->name( "admin.menus.update" );
    Route::post( "menus/delete/{id}", [ "uses" => "MenuController@__delete" ] )->name( "admin.menus.delete" );

    Route::get( "links", [ "uses" => "LinksController@index" ] )->name( "admin.links.all" );
    Route::get( "links/edit/{id}", [ "uses" => "LinksController@showEditPage" ] )->name( "admin.links.edit" );
    Route::post( "links/create", [ "uses" => "LinksController@__insert" ] )->name( "admin.links.create" );
    Route::post( "links/update/{id}", [ "uses" => "LinksController@__update" ] )->name( "admin.links.update" );
    Route::post( "links/delete/{id}", [ "uses" => "LinksController@__delete" ] )->name( "admin.links.delete" );

    //#! Dynamic routes for custom post types
    //#! Must check for table existence because artisan migrate command will break here if the table is not found
    if ( Schema::hasTable( 'post_types' ) ) {
        $postTypes = PostType::all();
        foreach ( $postTypes as $postType ) {
            // views dir: views/admin/post
            $baseRoute = "admin.{$postType->name}";

            //#! Post types
            Route::get( "{$postType->name}", [ "uses" => "PostsController@index" ] )->name( "{$baseRoute}.all" );
            Route::get( "{$postType->name}/new/{id?}", [ "uses" => "PostsController@showCreatePage" ] )->name( "{$baseRoute}.new" );
            Route::get( "{$postType->name}/edit/{id}", [ "uses" => "PostsController@showEditPage" ] )->name( "{$baseRoute}.edit" );
            Route::get( "{$postType->name}/translate/{id}/{code}/{new_post_id?}", [ "uses" => "PostsController@showTranslatePage" ] )->name( "{$baseRoute}.translate" );
            Route::get( "{$postType->name}/delete/{id}", [ "uses" => "PostsController@__delete" ] )->name( "{$baseRoute}.delete" );

            //#! Post type > Categories (all)  /admin/post/category & /new
            Route::get( "{$postType->name}/category", [ "uses" => "CategoriesController@index" ] )->name( "{$baseRoute}.category.all" );
            Route::get( "{$postType->name}/category/edit/{id}", [ "uses" => "CategoriesController@showEditPage" ] )->name( "{$baseRoute}.category.edit" );
            Route::post( "{$postType->name}/category/insert", [ "uses" => "CategoriesController@__insert" ] )->name( "{$baseRoute}.category.new" );
            Route::post( "{$postType->name}/category/update/{id}", [ "uses" => "CategoriesController@__update" ] )->name( "{$baseRoute}.category.update" );
            Route::get( "{$postType->name}/category/delete/{id}", [ "uses" => "CategoriesController@__delete" ] )->name( "{$baseRoute}.category.delete" );

            // @param category_id the id of the category being translated
            // @param language_id the language the category is translated into
            Route::get( "{$postType->name}/category/translate/{category_id}/{language_id}", [ "uses" => "CategoriesController@__translateCreate" ] )->name( "{$baseRoute}.category.translate" );

            //#! Post type > Tags (all)  /admin/post/tag & /new
            Route::get( "{$postType->name}/tag", [ "uses" => "TagsController@index" ] )->name( "{$baseRoute}.tag.all" );
            Route::get( "{$postType->name}/tag/edit/{id}", [ "uses" => "TagsController@showEditPage" ] )->name( "{$baseRoute}.tag.edit" );
            Route::post( "{$postType->name}/tag/insert", [ "uses" => "TagsController@__insert" ] )->name( "{$baseRoute}.tag.new" );
            Route::post( "{$postType->name}/tag/update/{id}", [ "uses" => "TagsController@__update" ] )->name( "{$baseRoute}.tag.update" );
            Route::get( "{$postType->name}/tag/delete/{id}", [ "uses" => "TagsController@__delete" ] )->name( "{$baseRoute}.tag.delete" );
            Route::post( "{$postType->name}/tag/translate/{language_id}", [ "uses" => "TagsController@__translate" ] )->name( "{$baseRoute}.tag.translate" );

            //#! Post type > Comments (all)  /admin/post/comment & /new
            Route::get( "{$postType->name}/comment/{post_id?}", [ "uses" => "CommentsController@index" ] )->name( "{$baseRoute}.comment.all" );
            Route::get( "{$postType->name}/comment/edit/{id}", [ "uses" => "CommentsController@showCommentEditPage" ] )->name( "{$baseRoute}.comment.edit" );
            Route::get( "{$postType->name}/comment/reply/{post_id}/{comment_id?}", [ "uses" => "CommentsController@showCommentReplyPage" ] )->name( "{$baseRoute}.comment.reply" );
            Route::post( "{$postType->name}/comment/insert", [ "uses" => "CommentsController@__insertComment" ] )->name( "{$baseRoute}.comment.insert" );
            Route::post( "{$postType->name}/comment/update/{id}", [ "uses" => "CommentsController@__updateComment" ] )->name( "{$baseRoute}.comment.update" );
            Route::get( "{$postType->name}/comment/delete/{id}", [ "uses" => "CommentsController@__deleteComment" ] )->name( "{$baseRoute}.comment.delete" );
        }
    }
} );

/*
 * #! Urls for post types
 * !! [Super important]
 * !! Must be right here, after admin routes or it will break the admin interface
 */
if ( Schema::hasTable( 'post_types' ) ) {
    $postTypes = PostType::all();
    foreach ( $postTypes as $postType ) {
        //#! posts/post-slug
        if ( 'post' == $postType->name ) {
            Route::get( 'posts/{slug}', "SiteController@post_view" )->middleware( [ 'web', 'active_user', 'under_maintenance' ] )->name( "app.post.view" );
        }
        //#!/post-slug
        elseif ( 'page' == $postType->name ) {
            Route::get( '/{slug}', "SiteController@post_view" )->middleware( [ 'web', 'active_user', 'under_maintenance' ] )->name( "app.page.view" );
        }
        //#! {post_type}/post-slug
        else {
            Route::get( $postType->name . '/{slug}', "SiteController@post_view" )->middleware( [ 'web', 'active_user', 'under_maintenance' ] )->name( "app.{$postType->name}.view" );
        }
    }
}

/*
 * Load routes from plugins
 * Plugins can use this action to inject their own routes
 */
do_action( 'contentpress/plugins/loaded' );
