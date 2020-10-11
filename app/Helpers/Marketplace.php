<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Marketplace
{
    /**
     * Stores the reference to the instance of the Cache class
     * @var Cache
     */
    private $cache;

    /**
     * Marketplace constructor.
     */
    public function __construct()
    {
        $this->cache = app( 'cp.cache' );
        $this->getPlugins();
        $this->getThemes();
    }

    public function getPlugins()
    {
        //#! Check cache first
        $cacheName = 'api-marketplace-plugins';
        $data = $this->cache->get( $cacheName, [] );

        if ( empty( $data ) ) {
            //#! Get from API
            $url = path_combine( CONTENTPRESS_API_URL, 'plugins' );
            $response = Http::get( $url )->json();
            if ( empty( $response ) || empty( $response[ 'data' ] ) ) {
                return [];
            }
            $data = $response[ 'data' ];
            $this->cache->set( $cacheName, $data );
        }
        return $data;
    }

    public function getThemes()
    {
        //#! Check cache first
        $cacheName = 'api-marketplace-themes';
        $data = $this->cache->get( $cacheName, [] );

        if ( empty( $data ) ) {
            //#! Get from API
            $url = path_combine( CONTENTPRESS_API_URL, 'themes' );
            $response = Http::get( $url )->json();
            if ( empty( $response ) || empty( $response[ 'data' ] ) ) {
                return [];
            }
            $data = $response[ 'data' ];
            $this->cache->set( $cacheName, $data );
        }
        return $data;
    }

    /**
     * Download and install a plugin from the marketplace
     * @param string $pluginDirName
     * @param string|float $pluginVersion
     * @return bool
     * @throws \Exception
     */
    public function installPlugin( string $pluginDirName, $pluginVersion )
    {
        $url = path_combine( CONTENTPRESS_API_URL, 'get_plugin', $pluginDirName, $pluginVersion );
        $response = Http::get( $url );
        if ( empty( $response ) ) {
            throw new \Exception( __( 'a.An error occurred while contacting the API server.' ) );
        }

        //#! Save to temp dir
        $tmpDirPath = public_path( 'uploads/tmp/' . $pluginDirName );
        if ( !File::isDirectory( $tmpDirPath ) ) {
            File::makeDirectory( $tmpDirPath, 0777, true );
        }
        $archiveFilePath = path_combine( $tmpDirPath, "{$pluginDirName}.zip" );
        File::put( $archiveFilePath, $response );

        //#! Extract the archive
        $zip = new \ZipArchive();
        if ( $zip->open( $archiveFilePath ) ) {
            $zip->extractTo( $tmpDirPath );
            $zip->close();

            $pluginsManager = PluginsManager::getInstance();

            //#! Move the extracted dir to plugins
            //#! Get the directory inside the uploads/tmp/$archiveName
            $pluginTmpDirPath = path_combine( $tmpDirPath, $pluginDirName );
            $pluginDestDirPath = path_combine( $pluginsManager->getPluginsDir(), $pluginDirName );
            File::moveDirectory( $pluginTmpDirPath, $pluginDestDirPath );
            File::deleteDirectory( $tmpDirPath );

            //#! Validate the uploaded plugin
            $pluginInfo = $pluginsManager->getPluginInfo( $pluginDirName );
            if ( false === $pluginInfo ) {
                File::deleteDirectory( $pluginDestDirPath );
                throw new \Exception( __( 'a.The installed plugin is not valid.' ) );
            }
        }
        return true;
    }

    /**
     * Download and install a theme from the marketplace
     * @param string $themeDirName
     * @param string|float $themeVersion
     * @return bool
     * @throws \Exception
     */
    public function installTheme( string $themeDirName, $themeVersion )
    {
        $url = path_combine( CONTENTPRESS_API_URL, 'get_theme', $themeDirName, $themeVersion );
        $response = Http::get( $url );
        if ( empty( $response ) ) {
            throw new \Exception( __( 'a.An error occurred while contacting the API server.' ) );
        }

        //#! Make the temp dir
        $tmpDirPath = public_path( 'uploads/tmp/' . $themeDirName );
        if ( !File::isDirectory( $tmpDirPath ) ) {
            File::makeDirectory( $tmpDirPath, 0777, true );
        }
        $archiveFilePath = path_combine( $tmpDirPath, "{$themeDirName}.zip" );
        File::put( $archiveFilePath, $response );

        //#! Extract the archive
        $zip = new \ZipArchive();
        if ( $zip->open( $archiveFilePath ) ) {
            $zip->extractTo( $tmpDirPath );
            $zip->close();

            $themesManager = ThemesManager::getInstance();

            //#! Move the extracted dir to themes
            //#! Get the directory inside the uploads/tmp/$archiveName
            $themeTmpDirPath = path_combine( $tmpDirPath, $themeDirName );
            $saveDirPath = path_combine( $themesManager->getThemesDirectoryPath(), $themeDirName );
            File::moveDirectory( $themeTmpDirPath, $saveDirPath );
            File::deleteDirectory( $tmpDirPath );

            //#! Validate the uploaded theme
            $theme = new Theme( $themeDirName );
            $themeInfo = $theme->getThemeData();
            if ( empty( $themeInfo ) ) {
                File::deleteDirectory( $saveDirPath );
                throw new \Exception( __( 'a.The installed theme is not valid.' ) );
            }
        }
        return true;
    }
}
