<?php

namespace App\Helpers;

use App\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Class PluginsManager
 * @package App\Helpers
 *
 * Standard Singleton
 */
class PluginsManager
{
    /**
     * Stores the name of the option holding the list of all active plugins
     * @var string
     */
    const ACTIVE_PLUGINS_OPT_NAME = '_app_active_plugins';

    const NOTICE_TYPE_SUCCESS = 'success';
    const NOTICE_TYPE_INFO = 'info';
    const NOTICE_TYPE_WARNING = 'warning';
    const NOTICE_TYPE_ERROR = 'error';

    /**
     * Holds the system path to the plugins directory
     * @var string
     */
    private $pluginsDir = '';

    /**
     * Stores the reference to the instance of the App\Options class
     * @var Options|null
     */
    private $optionsClass = null;

    /**
     * Stores the list of all plugins
     * @var array
     */
    private $allPlugins = [];

    /**
     * Stores the list of all active plugins
     * @var array
     */
    private $activePlugins = [];

    /**
     * @var PluginsManager|null
     */
    private static $instance = null;

    /**
     * Stores the type of the notice
     * @var string
     */
    private $_noticeType = '';

    /**
     * Stores the content of the notice
     * @var array
     */
    private $_notices = [];

    /**
     * PluginsManager constructor.
     */
    private function __construct()
    {
        if ( Schema::hasTable( 'options' ) ) {
            $this->pluginsDir = untrailingslashit( wp_normalize_path( public_path( 'plugins' ) ) );
            $this->optionsClass = new Options();

            $this->__checkDir();
            $this->getActivePlugins();

            //#! Since this is rather an expensive call we'll not trigger it here, instead we'll delegate this task to the PluginsController or user's choice
            //$this->getAllPlugins();

            $this->__loadActivePlugins();

            add_action( 'contentpress/plugin/activate', [ $this, '__doAction_PluginActivate' ] );
            add_action( 'contentpress/plugin/deactivate', [ $this, '__doAction_PluginDeactivate' ] );
            add_action( 'contentpress/plugin/delete', [ $this, '__doAction_PluginDelete' ] );
        }
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return PluginsManager|null
     */
    public static function getInstance()
    {
        if ( !self::$instance || !( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Activate the specified list of plugins
     * @param array $plugins
     * @hooked contentpress/plugin/activated
     */
    public function activatePlugins( $plugins = [] )
    {
        if ( !empty( $plugins ) ) {
            foreach ( $plugins as $pluginDirName ) {
                do_action( 'contentpress/plugin/activate', $pluginDirName );
            }
        }
    }

    /**
     * Deactivate the specified list of plugins
     * @param array $plugins
     * @hooked contentpress/plugin/deactivate
     */
    public function deactivatePlugins( $plugins = [] )
    {
        if ( !empty( $plugins ) ) {
            foreach ( $plugins as $pluginDirName ) {
                do_action( 'contentpress/plugin/deactivate', $pluginDirName );
            }
        }
    }

    /**
     * Check to see whether or not the specified plugin is active
     * @param string $pluginDirName
     * @return bool
     */
    public function isActivePlugin( $pluginDirName )
    {
        return isset( $this->activePlugins[ $pluginDirName ] );
    }

    /**
     * Retrieve the list of all plugins found in the plugins directory as an associative array [pluginDirName => $pluginDirPath]
     * @return array
     */
    public function getAllPlugins()
    {
        if ( !empty( $this->allPlugins ) ) {
            return $this->allPlugins;
        }
        $dirs = File::directories( $this->pluginsDir );
        if ( !empty( $dirs ) ) {
            foreach ( $dirs as $dirPath ) {
                $dirName = File::basename( $dirPath );
                $this->allPlugins[ $dirName ] = $this->getPluginInfo( $dirName );
            }
        }
        return $this->allPlugins;
    }

    /**
     * Retrieve the list of all active plugins as an associated array [pluginDirName => pluginAutoloadFilePath]
     * @return array
     */
    public function getActivePlugins()
    {
        if ( empty( $this->activePlugins ) ) {
            $this->activePlugins = $this->optionsClass->getOption( self::ACTIVE_PLUGINS_OPT_NAME, [] );
        }
        return $this->activePlugins;
    }

    /**
     * Retrieve the plugin's information as an associated array from the plugin's configuration file
     * @param string $pluginDirName
     * @return bool|object
     */
    public function getPluginInfo( $pluginDirName )
    {
        $pluginDirPath = path_combine( $this->pluginsDir, $pluginDirName );
        if ( File::isDirectory( $pluginDirPath ) ) {
            $pluginConfigFile = path_combine( $pluginDirPath, 'config.json' );
            if ( !File::isFile( $pluginConfigFile ) ) {
                return false;
            }

            $pluginInfo = json_decode( File::get( $pluginConfigFile ) );
            if ( $pluginInfo ) {
                return $pluginInfo;
            }
        }
        return false;
    }

    /**
     * Retrieve the system path to the specified plugin directory
     * @param string $pluginDirName
     * @param bool|false $relative Whether or not to retrieve the path relative to the application root directory
     * @return string
     */
    public function getPluginDirPath( $pluginDirName, $relative = false )
    {
        if ( $relative ) {
            return "plugins/{$pluginDirName}";
        }
        return path_combine( $this->pluginsDir, $pluginDirName );
    }

    /**
     * Check to see whether or not athe specified plugin exists in the plugins directory
     * @param string $pluginDirName
     * @return bool
     */
    public function exists( $pluginDirName )
    {
        return File::isDirectory( path_combine( $this->pluginsDir, $pluginDirName ) );
    }

    /**
     * Activate a plugin
     * @param string $pluginDirName
     * @hooked contentpress/plugin/activated
     */
    public function __doAction_PluginActivate( $pluginDirName )
    {
        if ( $this->isActivePlugin( $pluginDirName ) ) {
            return;
        }

        $pluginInfo = $this->getPluginInfo( $pluginDirName );
        if ( !$pluginInfo ) {
            $this->__addNotice( self::NOTICE_TYPE_ERROR, __( 'a.:name plugin could not be activated. The plugin was not found.', [ 'name' => $pluginInfo->display_name ] ) );
            return;
        }
        $this->activePlugins[ $pluginDirName ] = $pluginInfo->autoload;
        $r = $this->optionsClass->addOption( self::ACTIVE_PLUGINS_OPT_NAME, $this->activePlugins );
        if ( $r ) {
            $this->__addNotice( self::NOTICE_TYPE_SUCCESS, __( 'a.:name plugin has been activated', [ 'name' => $pluginInfo->display_name ] ) );
            $this->__loadPluginFile( $pluginDirName, $pluginInfo->autoload );
            do_action( 'contentpress/plugin/activated', $pluginDirName, $pluginInfo );
        }
        else {
            $this->__addNotice( self::NOTICE_TYPE_ERROR, __( 'a.:name plugin could not be activated', [ 'name' => $pluginInfo->display_name ] ) );
        }
    }

    /**
     * Activate a plugin
     * @param string $pluginDirName
     * @hooked contentpress/plugin/deactivated
     */
    public function __doAction_PluginDeactivate( $pluginDirName )
    {
        if ( !$this->isActivePlugin( $pluginDirName ) ) {
            return;
        }
        $pluginInfo = $this->getPluginInfo( $pluginDirName );
        if ( !$pluginInfo ) {
            $this->__addNotice( self::NOTICE_TYPE_ERROR, __( 'a.:name plugin has been deactivated. The plugin was not found.', [ 'name' => $pluginInfo->name ] ) );
            return;
        }
        unset( $this->activePlugins[ $pluginDirName ] );
        $r = $this->optionsClass->addOption( self::ACTIVE_PLUGINS_OPT_NAME, $this->activePlugins );
        if ( $r ) {
            $this->__addNotice( self::NOTICE_TYPE_SUCCESS, __( 'a.:name plugin has been deactivated', [ 'name' => $pluginInfo->display_name ] ) );
            do_action( 'contentpress/plugin/deactivated', $pluginDirName, $pluginInfo );
        }
        else {
            $this->__addNotice( self::NOTICE_TYPE_ERROR, __( 'a.:name plugin could not be deactivated', [ 'name' => $pluginInfo->display_name ] ) );
        }
    }

    /**
     * Delete a plugin
     * @param string $pluginDirName
     * @hooked contentpress/plugin/deleted
     */
    public function __doAction_PluginDelete( $pluginDirName )
    {
        $pluginInfo = $this->getPluginInfo( $pluginDirName );
        $pluginDirPath = $this->getPluginDirPath( $pluginDirName );
        $uninstallFile = path_combine( $pluginDirPath, 'uninstall.php' );
        if ( File::isFile( $uninstallFile ) ) {
            File::requireOnce( $uninstallFile );
            do_action( 'contentpress/plugin/deleted', $pluginDirName );
        }

        if ( File::deleteDirectory( $pluginDirPath ) ) {
            $this->__addNotice( self::NOTICE_TYPE_SUCCESS, __( 'a.:name plugin has been deleted', [ 'name' => $pluginInfo->display_name ] ) );
            do_action( 'contentpress/plugin/deleted', $pluginDirName );
        }
    }

    /**
     * Check to see whether or not the plugins directory exists and create it if it doesn't
     */
    private function __checkDir()
    {
        if ( !File::isDirectory( $this->pluginsDir ) ) {
            File::makeDirectory( $this->pluginsDir );
        }
    }

    /**
     * Load active plugins
     * @triggers plugins_loaded
     */
    private function __loadActivePlugins()
    {
        $plugins = $this->getActivePlugins();
        if ( empty( $plugins ) ) {
            if ( !did_action( 'contentpress/plugins/loaded' ) ) {
                do_action( 'contentpress/plugins/loaded' );
            }
            return;
        }

        foreach ( $plugins as $pluginDirName => $autoloadFileName ) {
            if ( !$this->__loadPluginFile( $pluginDirName, $autoloadFileName ) ) {
                do_action( 'contentpress/plugin/deactivate', $pluginDirName );
            }
        }
        if ( !did_action( 'contentpress/plugins/loaded' ) ) {
            do_action( 'contentpress/plugins/loaded' );
        }
    }

    /**
     * Load plugin's file
     * @param $pluginDirName
     * @param $autoloadFileName
     * @return bool
     * @internal
     */
    private function __loadPluginFile( $pluginDirName, $autoloadFileName )
    {
        $filePath = path_combine( $this->pluginsDir, $pluginDirName, $autoloadFileName );
        if ( File::isFile( $filePath ) ) {
            require_once( $filePath );
            return true;
        }
        return false;
    }

    /**
     * Add a notice
     * @param string $type
     * @param string $notice
     */
    private function __addNotice( $type = self::NOTICE_TYPE_SUCCESS, $notice = '' )
    {
        $this->_noticeType = $type;
        array_push( $this->_notices, $notice );
    }

    /**
     * @return string
     */
    public function getPluginsDir(): string
    {
        return $this->pluginsDir;
    }

    /**
     * Retrieve the current notice
     * @return array('class', 'text')
     */
    public function getNotice()
    {
        return [
            'class' => $this->_noticeType,
            'text' => implode( ' ', $this->_notices ),
        ];
    }

}
