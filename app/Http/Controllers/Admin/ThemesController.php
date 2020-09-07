<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ScriptsManager;
use App\Helpers\Theme;
use Illuminate\Support\Facades\File;

class ThemesController extends AdminControllerBase
{
    public function index()
    {
        if ( !cp_current_user_can( 'list_themes' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'admin.themes-styles', asset( '_admin/css/themes/index.css' ) );

        ScriptsManager::localizeScript( 'themes-script-locale', 'ThemesLocale', [
            'text_confirm_delete' => __( 'a.Are you sure you want to delete this theme?' ),
            'text_error_theme_name_not_found' => __( 'a.The specified theme was not found.' ),
            'text_error' => __( 'a.Error' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'themes-index.js', asset( '_admin/js/themes/index.js' ) );

        //#! Filter the themes so the currently active theme shows up first in the list
        //#! Pick up new uploaded themes (if any) and update cache
        $this->themesManager->rebuildCache( false );
        $_themes = collect( $this->themesManager->getInstalledThemes() );
        $currentTheme = $this->themesManager->getActiveTheme();
        $currentThemeName = $currentTheme->get( 'name' );

        $themes = $_themes->filter( function ( $dirName ) use ( $currentThemeName ) {
            return ( $dirName != $currentThemeName );
        } )->toArray();
        array_unshift( $themes, $currentThemeName );

        return view( 'admin.themes.index' )->with( [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
        ] );
    }

    public function renderAddView()
    {
        if ( !cp_current_user_can( 'install_themes' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );
        ScriptsManager::localizeScript( 'themes-script-locale', 'ThemesLocale', [
            'text_theme_uploaded' => __( 'a.Theme uploaded' ),
            'text_theme_deleted' => __( 'a.Theme deleted' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'themes-add.js', asset( '_admin/js/themes/add.js' ) );

        return view( 'admin.themes.add' );
    }

    public function __activate( $themeDirName )
    {
        if ( !cp_current_user_can( 'switch_themes' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $activeTheme = $this->themesManager->getActiveTheme();

        //#! Get the current theme name before it's changed
        $oldThemeName = $activeTheme->get( 'name' );

        //#! On Activate, ensure the parent theme exists if $themeName is a child theme
        if ( $activeTheme->isChildTheme() ) {
            $parent = $activeTheme->getParentTheme();
            if ( !$parent->parentThemeDirExists() ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.An error occurred. The theme you are trying to activate is missing its parent theme.' ),
                ] );
            }
        }

        //#! Activate the theme
        do_action( 'contentpress/switch_theme', $themeDirName, $oldThemeName );
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Theme activated.' ),
        ] );
    }

    public function __delete( $themeDirName )
    {
        if ( !cp_current_user_can( 'delete_themes' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        if ( $this->themesManager->getActiveTheme()->get( 'name' ) == $themeDirName ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You cannot delete the currently active theme.' ),
            ] );
        }

        //#! Prevent deleting the theme if the specified theme is a child of it
        $themes = $this->themesManager->getInstalledThemes();
        foreach ( $themes as $themeDir ) {
            $theme = new Theme( $themeDir );
            if ( $theme->get( 'extends' ) == $themeDirName ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.This theme cannot be deleted while it is the parent of an active theme.' ),
                ] );
            }
        }

        //#! Delete the theme's directory
        $theme = new Theme( $themeDirName );
        if ( File::deleteDirectory( $theme->getDirPath() ) ) {
            do_action( 'contentpress/theme_deleted', $themeDirName );
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Theme deleted.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred while trying to delete the theme.' ),
        ] );
    }
}
