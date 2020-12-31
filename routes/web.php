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
use App\Http\Controllers\Admin\TranslationsController;
use App\Models\PostType;
use App\Models\Settings;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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
//#! The Email Verification Handler
Route::get( '/email/verify/{id}/{hash}', function ( EmailVerificationRequest $request ) {
    $request->fulfill();
    return redirect( '/' );
} )->middleware( [ 'auth', 'signed' ] )->name( 'verification.verify' );
//#! Resending The Verification Email
Route::post( '/email/verification-notification', function ( Request $request ) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with( 'message', __( 'a.Verification link sent!' ) );
} )->middleware( [ 'auth', 'throttle:6,1' ] )->name( 'verification.send' );


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

    //#! Dashboard
    Route::get( "/", [ "uses" => "DashboardController@index" ] )->name( "admin.dashboard" );
    Route::get( "dashboard_edit", [ "uses" => "DashboardController@showEditDashboardView" ] )->name( "admin.dashboard.edit" );
    Route::get( "dashboard_commands", [ "uses" => "DashboardController@showCommandsView" ] )->name( "admin.dashboard.commands" );
    Route::post( "dashboard_refresh_stats", [ "uses" => "DashboardController@__refreshStats" ] )->name( "admin.dashboard.refresh_stats" );
    Route::post( "dashboard_commands/reset", [ "uses" => "DashboardController@__cmdReset" ] )->name( "admin.dashboard.reset" );
    Route::post( "dashboard_commands/reinstall", [ "uses" => "DashboardController@__cmdReinstall" ] )->name( "admin.dashboard.reinstall" );
    Route::post( "dashboard_commands/clear-cache", [ "uses" => "DashboardController@__cmdClearAppCache" ] )->name( "admin.dashboard.clear_cache" );
    Route::post( "dashboard_commands/composer-update", [ "uses" => "DashboardController@__cmdComposerUpdate" ] )->name( "admin.dashboard.composer_update" );
    Route::post( "dashboard_commands/composer-dump", [ "uses" => "DashboardController@__cmdComposerDumpAutoload" ] )->name( "admin.dashboard.composer_dump" );

    Route::get( "updates", [ "uses" => "DashboardController@showUpdatesView" ] )->name( "admin.dashboard.updates" );
    Route::post( "updates/check", [ "uses" => "DashboardController@__checkForUpdates" ] )->name( "admin.dashboard.check_for_updates" );
    Route::post( "updates/force-check", [ "uses" => "DashboardController@__forceCheckForUpdates" ] )->name( "admin.dashboard.force_check_for_updates" );
    Route::post( "update/theme/{file_name}", [ "uses" => "DashboardController@__update_theme" ] )->name( "admin.dashboard.update.theme" );
    Route::post( "update/plugin/{file_name}", [ "uses" => "DashboardController@__update_plugin" ] )->name( "admin.dashboard.update.plugin" );
    Route::post( "update/core/{version}", [ "uses" => "DashboardController@__update_core" ] )->name( "admin.dashboard.update.core" );

    //#! Users
    Route::get( "users", [ "uses" => "UsersController@index" ] )->name( "admin.users.all" );
    Route::get( "users/add", [ "uses" => "UsersController@showCreatePage" ] )->name( "admin.users.add" );
    Route::get( "users/edit/{id}", [ "uses" => "UsersController@showEditPage" ] )->name( "admin.users.edit" )->middleware( 'password.confirm' );
    Route::get( "users/block/{id}", [ "uses" => "UsersController@__block" ] )->name( "admin.users.block" );
    Route::get( "users/unblock/{id}", [ "uses" => "UsersController@__unblock" ] )->name( "admin.users.unblock" );
    Route::get( "users/delete/{id}", [ "uses" => "UsersController@__delete" ] )->name( "admin.users.delete" );
    Route::post( "users/insert", [ "uses" => "UsersController@__insert" ] )->name( "admin.users.insert" );
    Route::post( "users/update", [ "uses" => "UsersController@__update" ] )->name( "admin.users.update" );
    Route::post( "users/profile/update/{id}", [ "uses" => "UsersController@__updateProfile" ] )->name( "admin.users.update_profile" );

    //#! Roles & Capabilities
    Route::get( "roles", [ "uses" => "RolesController@showRolesPage" ] )->name( "admin.roles.all" );
    Route::get( "roles/edit/{id}", [ "uses" => "RolesController@showRoleEditPage" ] )->name( "admin.roles.edit" );
    Route::post( "roles/update/{id}", [ "uses" => "RolesController@updateRole" ] )->name( "admin.roles.update" );
    Route::get( "roles/add", [ "uses" => "RolesController@showRoleCreatePage" ] )->name( "admin.roles.add" );
    Route::post( "roles/create", [ "uses" => "RolesController@createRole" ] )->name( "admin.roles.create" );
    Route::post( "roles/delete/{id}", [ "uses" => "RolesController@deleteRole" ] )->name( "admin.roles.delete" );
    Route::get( "roles/capabilities", [ "uses" => "RolesController@showCapabilitiesPage" ] )->name( "admin.roles.capabilities" );
    Route::post( "roles/capabilities/update", [ "uses" => "RolesController@updateRoleCapabilities" ] )->name( "admin.roles.capabilities.update" );


    //#! Settings
    Route::get( "settings", [ "uses" => "SettingsController@index" ] )->name( "admin.settings.all" );
    Route::get( "settings/languages", [ "uses" => "SettingsController@languages" ] )->name( "admin.settings.languages" );
    Route::get( "settings/reading", [ "uses" => "SettingsController@reading" ] )->name( "admin.settings.reading" );
    Route::get( "settings/post_types", [ "uses" => "SettingsController@post_types" ] )->name( "admin.settings.post_types" );
    Route::get( "settings/post_types/edit/{id}", [ "uses" => "SettingsController@showPostTypeEditPage" ] )->name( "admin.settings.post_types.edit" );
    Route::get( "settings/post_types/delete/{id}", [ "uses" => "SettingsController@__deletePostType" ] )->name( "admin.settings.post_types.delete" );
    Route::get( "settings/cache/clear", [ "uses" => "SettingsController@__clearCache" ] )->name( "admin.cache.clear" );

    Route::post( "settings/general/update", [ "uses" => "SettingsController@__updateSettings" ] )->name( "admin.settings.general.update" );
    Route::post( "settings/languages/update", [ "uses" => "SettingsController@__updateLanguages" ] )->name( "admin.settings.languages.update" );
    Route::post( "settings/languages/add", [ "uses" => "SettingsController@__addLanguage" ] )->name( "admin.settings.languages.add" );
    Route::post( "settings/languages/delete/{id}", [ "uses" => "SettingsController@__deleteLanguage" ] )->name( "admin.settings.languages.delete" );
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
    Route::get( "plugins/marketplace", [ "uses" => "PluginsController@__viewMarketplace" ] )->name( "admin.plugins.marketplace" );
    Route::post( "plugins/marketplace/install/{plugin_dir_name}/{version}", [ "uses" => "PluginsController@__marketplaceInstallPlugin" ] )->name( "admin.plugins.marketplace.install" );
    Route::get( "plugins/marketplace/refresh", [ "uses" => "PluginsController@refresh" ] )->name( "admin.plugins.marketplace.refresh" );

    //#! Themes
    Route::get( "themes", [ "uses" => "ThemesController@index" ] )->name( "admin.themes.all" );
    Route::get( "themes/add", [ "uses" => "ThemesController@renderAddView" ] )->name( "admin.themes.add" );
    Route::get( "themes/activate/{theme_name}", [ "uses" => "ThemesController@__activate" ] )->name( "admin.themes.activate" );
    Route::get( "themes/delete/{theme_name}", [ "uses" => "ThemesController@__delete" ] )->name( "admin.themes.delete" );
    Route::get( "themes/marketplace", [ "uses" => "ThemesController@__viewMarketplace" ] )->name( "admin.themes.marketplace" );
    Route::post( "themes/marketplace/install/{theme_dir_name}/{version}", [ "uses" => "ThemesController@__marketplaceInstallTheme" ] )->name( "admin.themes.marketplace.install" );
    Route::get( "themes/marketplace/refresh", [ "uses" => "ThemesController@refresh" ] )->name( "admin.themes.marketplace.refresh" );

    //#! Media
    Route::get( "media", [ "uses" => "MediaController@index" ] )->name( "admin.media.all" );
    Route::get( "media/add", [ "uses" => "MediaController@showAddView" ] )->name( "admin.media.add" );
    Route::get( "media/edit/{id}", [ "uses" => "MediaController@showEditView" ] )->name( "admin.media.edit" );
    Route::any( "media/search/{s?}", [ "uses" => "MediaController@showSearchView" ] )->name( "admin.media.search" );
    Route::post( "media/update/{id}", [ "uses" => "MediaController@__update" ] )->name( "admin.media.update" );
    Route::post( "media/delete/{id}", [ "uses" => "MediaController@__delete" ] )->name( "admin.media.delete" );

    //#! Menus
    Route::get( "menus", [ "uses" => "MenuController@index" ] )->name( "admin.menus.all" );
    Route::get( "menus/add", [ "uses" => "MenuController@showCreatePage" ] )->name( "admin.menus.add" );
    Route::get( "menus/edit/{id}", [ "uses" => "MenuController@showEditPage" ] )->name( "admin.menus.edit" );
    Route::post( "menus/create", [ "uses" => "MenuController@__insert" ] )->name( "admin.menus.create" );
    Route::post( "menus/update/{id}", [ "uses" => "MenuController@__update" ] )->name( "admin.menus.update" );
    Route::post( "menus/delete/{id}", [ "uses" => "MenuController@__delete" ] )->name( "admin.menus.delete" );

    //#! Translations
    Route::get( "translations", [ TranslationsController::class, "index" ] )->name( "admin.translations.core" );
    Route::post( "translations/update", [ TranslationsController::class, "__updateTranslation" ] )->name( "admin.translations.update" );
    Route::get( "translations/plugins", [ TranslationsController::class, "plugins" ] )->name( "admin.translations.plugins" );
    Route::get( "translations/themes", [ TranslationsController::class, "themes" ] )->name( "admin.translations.themes" );
    Route::post( "translations/plugins/create", [ TranslationsController::class, "__pluginCreateTranslation" ] )->name( "admin.translations.plugins.create" );
    Route::post( "translations/themes/create", [ TranslationsController::class, "__themeCreateTranslation" ] )->name( "admin.translations.themes.create" );

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
            Route::post( "{$postType->name}/delete", [ "uses" => "PostsController@__deleteMultiple" ] )->name( "{$baseRoute}.delete_selected" );

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
do_action( 'valpress/plugins/loaded' );
