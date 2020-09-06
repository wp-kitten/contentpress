<?php

namespace App\Helpers;

use App\Options;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ThemesManager
{
    /**
     * Stores the name of the option holding the list of all installed themes
     * @var string
     */
    const INSTALLED_THEMES_OPT_NAME = '_app_installed_themes';

    /**
     * Stores the name of the option holding the name of the currently active theme
     */
    const ACTIVE_THEME_NAME_OPT_NAME = '_active_theme';

    /**
     * Stores the reference to the instance of this class
     * @var ThemesManager|null
     */
    private static $instance = null;

    /**
     * Stores the list of all installed themes
     * @var array
     */
    private $_installedThemes = [];

    /**
     * Stores the reference to the instance of the Options class
     * @var Options|null
     */
    private $optionsClass = null;

    /**
     * Stores the reference to the instance of the UserNotices class
     * @var UserNotices|null
     */
    private $noticesClass = null;

    /**
     * Stores the system path to the themes directory
     * @var string
     */
    private $themesDir = '';

    /**
     * Stores the reference to the instance of the active theme
     * @var Theme|null
     */
    private $_activeTheme = null;

    /**
     * ThemesManager constructor.
     */
    private function __construct()
    {
        if ( Schema::hasTable( 'options' ) ) {
            $this->optionsClass = new Options();
            $this->noticesClass = UserNotices::getInstance();
            $this->themesDir = untrailingslashit( wp_normalize_path( public_path( 'themes' ) ) );

            if ( !$this->__checkThemesDir() ) {
                $this->noticesClass->addNotice( 'warning', __( 'a.The themes directory :dir_path was not found nor could it be created. check for permissions.', [
                    'dir_path' => $this->themesDir,
                ] ) );
                return;
            }

            //#! Check the installed themes
            if ( !$this->__checkInstalledThemes() ) {
                return;
            }

            //#! Load the active theme
            if ( !$this->__loadActiveTheme() ) {
                return;
            }

            add_action( 'contentpress/theme_deleted', [ $this, '_action_theme_deleted' ], 20, 1 );
            add_action( 'contentpress/switch_theme', [ $this, '_action_theme_activated' ], 20, 2 );
        }
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return ThemesManager|null
     */
    public static function getInstance(): ?ThemesManager
    {
        if ( !self::$instance || !( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return Theme|null
     */
    public function getActiveTheme(): ?Theme
    {
        return $this->_activeTheme;
    }

    /**
     * Retrieve the system path to the themes directory
     * @return string
     */
    public function getThemesDirectoryPath(): string
    {
        return $this->themesDir;
    }

    /**
     * Retrieve the http path to the themes directory
     * @return Application|UrlGenerator|string
     */
    public function getThemesDirectoryUrl()
    {
        $path = str_ireplace( trailingslashit( wp_normalize_path( public_path() ) ), '', $this->themesDir );
        return url( $path );
    }

    /**
     * Retrieve the list of all installed themes (array of theme directory names)
     * @return array
     */
    public function getInstalledThemes(): array
    {
        return $this->_installedThemes;
    }

    /**
     * Save the current active theme in database
     * @param $themeDirName
     */
    public function saveActiveTheme( $themeDirName )
    {
        $this->optionsClass->addOption( self::ACTIVE_THEME_NAME_OPT_NAME, $themeDirName );
    }

    /**
     * Save the list of all installed themes in database
     */
    public function updateCache()
    {
        $this->optionsClass->addOption( self::INSTALLED_THEMES_OPT_NAME, $this->_installedThemes );
    }

    /**
     * Check to see whether the specified uploaded theme is a valid theme
     * @param string $uploadDirPath
     * @return array
     */
    public function checkThemeUploadDir( string $uploadDirPath ): array
    {
        $theme = new Theme( basename( $uploadDirPath ) );
        return ( $theme->isValid() ? [] : $theme->getErrors() );
    }

    /**
     * Update the list of all installed themes in the database when a theme is deleted
     *
     * @param $themeDirName
     *
     * @hooked add_action( 'contentpress/theme_deleted', [ $this, '_action_theme_deleted' ], 20, 1 );
     * @see __construct()
     */
    public function _action_theme_deleted( $themeDirName )
    {
        if ( !empty( $this->_installedThemes ) ) {
            $installed = [];
            foreach ( $this->_installedThemes as $dirName ) {
                if ( $dirName != $themeDirName ) {
                    $installed[] = $dirName;
                }
            }
            $this->_installedThemes = $installed;
            $this->updateCache();
        }
    }

    /**
     * Update the name of the active theme in the database
     * @param string $newDirName
     * @param string $oldDirName
     *
     * @hooked add_action( 'contentpress/switch_theme', [ $this, '_action_theme_activated' ], 20, 2 );
     * @see __construct()
     */
    public function _action_theme_activated( string $newDirName, string $oldDirName )
    {
        $this->saveActiveTheme( $newDirName );
    }

    /**
     * Check the themes directory for existence. Attempts to create it if missing
     */
    private function __checkThemesDir(): bool
    {
        if ( !File::isDirectory( $this->themesDir ) ) {
            File::makeDirectory( $this->themesDir );
        }
        return File::isDirectory( $this->themesDir );
    }

    /**
     * Attempts to load the currently active theme
     *
     * @uses action('contentpress/theme/activated', $themeDirName)
     */
    private function __loadActiveTheme()
    {
        //#! Attempt to instantiate the active theme saved in db
        $activeThemeName = $this->optionsClass->getOption( self::ACTIVE_THEME_NAME_OPT_NAME, '' );
        $activeTheme = null;
        if ( !empty( $activeThemeName ) ) {
            $activeTheme = new Theme( $activeThemeName );
            if ( $activeTheme->isValid() ) {
                $activeTheme->load();
                $this->_activeTheme = $activeTheme;
                return true;
            }
        }

        //#! Attempt to instantiate the default theme
        $defaultTheme = new Theme( env( 'DEFAULT_THEME_NAME', 'default' ) );
        if ( $defaultTheme->isValid() ) {

            //#! Activate the default theme
            $defaultTheme->load();
            $this->_activeTheme = $defaultTheme;

            return true;
        }

        //#! Add notice
        $this->noticesClass->addNotice( 'warning', __( 'a.The theme ":theme_name" could not be activated due to unexpected errors nor the default theme since it was not found.', [
            'theme_name' => $activeThemeName,
        ] ) );
        return false;
    }

    /**
     * @return bool
     */
    private function __checkInstalledThemes()
    {
        $errors = [];
        $installedThemes = $this->optionsClass->getOption( self::INSTALLED_THEMES_OPT_NAME, [] );

        //#! Scan the themes directory
        if ( empty( $installedThemes ) ) {
            $themes = glob( $this->themesDir . '/*', GLOB_ONLYDIR );
            $installedThemes = array_map( 'basename', $themes );
        }

        foreach ( $installedThemes as $themeDirName ) {
            $theme = new Theme( $themeDirName );
            if ( $theme->isValid() ) {
                $this->_installedThemes[] = $themeDirName;
                continue;
            }
            $errors[ "$themeDirName" ] = $theme->getErrors();
        }

        //#! Check for errors
        if ( !empty( $errors ) ) {
            //#! Combine messages so we won't display a notice for each error
            foreach ( $errors as $path => $messages ) {
                $this->noticesClass->addNotice( 'warning', implode( '<br/>', $messages ) );
            }
            return false;
        }
        return true;
    }
}
