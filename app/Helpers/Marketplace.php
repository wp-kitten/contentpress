<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Marketplace
{
    /**
     * Stores the list of plugins retrieved from the api
     * @var array
     */
    private $plugins = [];

    public function __construct()
    {
        $this->getPlugins();
    }

    public function getPlugins()
    {
        //#! Check cache first
        $cacheName = 'api-marketplace-plugins';
        /**
         * @var Cache $cache
         */
        $cache = app( 'cp.cache' );
        $plugins = $cache->get( $cacheName, [] );

        if ( empty( $plugins ) ) {
            //#! Get from API
            $url = path_combine( CONTENTPRESS_API_URL, 'plugins' );
            $response = Http::get( $url )->json();
            if ( empty( $response ) || empty( $response[ 'data' ] ) ) {
                return [];
            }
            $plugins = $response[ 'data' ];
            $cache->set( $cacheName, $plugins );
        }

        $this->plugins = $plugins;
        return $this->plugins;
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
}
