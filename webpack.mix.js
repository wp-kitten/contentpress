const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */


//@! sass
mix
    .sass('resources/sass/admin-helpers.scss','public/_admin/css')
;


//@! Frontend
mix
// .react('resources/js/app.js', 'public/js')
;

//@! Backend
//#! [Important] There cannot be a path like this: "public/admin" as long as there is an admin controller; you will get a 404 page when trying to access "site/admin"
mix
    //#! Backend scripts for pages
    .sass('resources/sass/_admin/categories/index.scss', 'public/_admin/css/categories')
    .js('resources/js/admin/_scripts/categories/index.js', 'public/_admin/js/categories')
    .js('resources/js/admin/_scripts/categories/edit.js', 'public/_admin/js/categories')
    //
    .js('resources/js/admin/_scripts/comments/index.js', 'public/_admin/js/comments')
    //
    .sass('resources/sass/_admin/dashboard/edit.scss', 'public/_admin/css/dashboard')
    .js('resources/js/admin/_scripts/dashboard/edit.js', 'public/_admin/js/dashboard')
    .js('resources/js/admin/_scripts/dashboard/index.js', 'public/_admin/js/dashboard')
    //
    .js('resources/js/admin/_scripts/posts/index.js', 'public/_admin/js/posts')
    .js('resources/js/admin/_scripts/posts/create.js', 'public/_admin/js/posts')
    .js('resources/js/admin/_scripts/posts/edit.js', 'public/_admin/js/posts')
    .js('resources/js/admin/_scripts/posts/translate.js', 'public/_admin/js/posts')
    //
    .js('resources/js/admin/_scripts/settings/index.js', 'public/_admin/js/settings')
    .js('resources/js/admin/_scripts/settings/edit.js', 'public/_admin/js/settings')
    .js('resources/js/admin/_scripts/settings/languages.js', 'public/_admin/js/settings')
    //
    .js('resources/js/admin/_scripts/users/edit.js', 'public/_admin/js/users')
    //
    .sass('resources/sass/_admin/menus/index.scss', 'public/_admin/css/menus')
    .js('resources/js/admin/_scripts/menus/edit.js', 'public/_admin/js/menus')
    .js('resources/js/admin/_scripts/menus/index.js', 'public/_admin/js/menus')
    //
    .sass('resources/sass/_admin/plugins/index.scss', 'public/_admin/css/plugins')
    .js('resources/js/admin/_scripts/plugins/index.js', 'public/_admin/js/plugins')
    .js('resources/js/admin/_scripts/plugins/add.js', 'public/_admin/js/plugins')
    //
    .sass('resources/sass/_admin/media/index.scss', 'public/_admin/css/media')
    .js('resources/js/admin/_scripts/media/index.js', 'public/_admin/js/media')
    .js('resources/js/admin/_scripts/media/add.js', 'public/_admin/js/media')
    .js('resources/js/admin/_scripts/media/edit.js', 'public/_admin/js/media')
    .js('resources/js/admin/_scripts/media/modal.js', 'public/_admin/js/media')
    //
    .sass('resources/sass/_admin/themes/index.scss', 'public/_admin/css/themes')
    .js('resources/js/admin/_scripts/themes/index.js', 'public/_admin/js/themes')
    .js('resources/js/admin/_scripts/themes/add.js', 'public/_admin/js/themes')


    //#! Copy the admin template css file
    .sass('resources/admin-template/sass/main.scss', 'public/_admin/css')


    //@! Backend global scripts: admin.js, underscore.js, popper.js, jquery.js
    .js('resources/js/admin/_scripts/admin.js', 'public/_admin/js')
    .js('resources/js/app-dependencies.js', 'public/_admin/js')
    .js('resources/js/admin/Utils.js', 'public/_admin/js')
    .js('resources/js/admin/sanitizer.js', 'public/_admin/js')
    .js('resources/js/admin/_scripts/admin-heartbeat.js', 'public/_admin/js')
    .js('resources/js/admin/components/ContentPressTextEditor.js', 'public/_admin/js')
    .js('resources/js/admin/components/DropifyImageUploader.js', 'public/_admin/js')
;
